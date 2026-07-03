<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Layanan;
use App\Models\Lokasi;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\HppLayanan;
use App\Models\Voucher;
use App\Jobs\KirimWaJob;
use App\Services\WhatsappService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(protected WhatsappService $wa) {}

    public function index(Request $request)
    {
        $orders = Order::with(['layanan', 'pembayaran', 'lokasi', 'pelanggan'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->cari, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('nama_pelanggan', 'like', "%{$request->cari}%")
                  ->orWhere('no_order', 'like', "%{$request->cari}%")
                  ->orWhere('no_hp', 'like', "%{$request->cari}%");
            }))
            ->latest()->paginate(15)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $layanans   = Layanan::where('aktif', true)->get();
        $lokasis    = Lokasi::where('aktif', true)->orderBy('kode')->get();
        $hppLayanan = HppLayanan::select('layanan_id', DB::raw('sum(biaya) as total_hpp'))
            ->groupBy('layanan_id')->pluck('total_hpp', 'layanan_id');

        return view('orders.create', compact('layanans', 'lokasis', 'hppLayanan'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_pelanggan'   => 'required|string|max:100',
            'no_hp'            => 'required|string|max:20',
            'layanan_id'       => 'required|exists:layanans,id',
            'jenis_sepatu'     => 'required|string|max:50',
            'merek'            => 'nullable|string|max:50',
            'warna'            => 'nullable|string|max:30',
            'kondisi'          => 'nullable|string|max:100',
            'jumlah_pasang'    => 'required|integer|min:1|max:20',
            'catatan'          => 'nullable|string|max:500',
            'estimasi_selesai' => 'required|date|after_or_equal:today',
            'metode_bayar'     => 'required|in:tempo,transfer,lunas,cash,qris',
            'lokasi_id'        => 'nullable|exists:lokasis,id',
            'catatan_lokasi'   => 'nullable|string|max:200',
            'hpp'              => 'nullable|integer|min:0',
            'voucher_kode'     => 'nullable|string|max:30',
        ]);

        $layanan      = Layanan::findOrFail($data['layanan_id']);
        $hargaSatuan  = $layanan->harga;
        $total        = $hargaSatuan * $data['jumlah_pasang'];

        if (!empty($data['lokasi_id'])) {
            $lokasi = Lokasi::with('layanans')->find($data['lokasi_id']);
            if ($lokasi) {
                $hargaSatuan = $lokasi->hitungHarga($layanan->harga, $layanan->id);
                $total       = $hargaSatuan * $data['jumlah_pasang'];
            }
        }

        if (empty($data['hpp'])) {
            $totalHpp   = HppLayanan::where('layanan_id', $data['layanan_id'])->sum('biaya');
            $data['hpp'] = $totalHpp * $data['jumlah_pasang'];
        }

        // Terapkan voucher
        $voucherId = null;
        $diskon    = 0;
        $voucherWarning = null;
        if (!empty($data['voucher_kode'])) {
            $voucher = Voucher::where('kode', strtoupper($data['voucher_kode']))->first();
            if ($voucher && $voucher->masihBerlaku($total)) {
                $diskon    = $voucher->hitungDiskon($total);
                $voucherId = $voucher->id;
                $voucher->increment('terpakai');
            } else {
                $voucherWarning = "Voucher '{$data['voucher_kode']}' tidak valid atau expired — order disimpan tanpa diskon.";
            }
        }

        $pelanggan = Pelanggan::firstOrCreate(
            ['no_hp' => $data['no_hp']],
            ['nama'  => $data['nama_pelanggan']]
        );
        if ($pelanggan->nama !== $data['nama_pelanggan']) {
            $pelanggan->update(['nama' => $data['nama_pelanggan']]);
        }

        $order = Order::create([
            'user_id'          => auth()->id(),
            'pelanggan_id'     => $pelanggan->id,
            'lokasi_id'        => $data['lokasi_id'] ?? null,
            'nama_pelanggan'   => $data['nama_pelanggan'],
            'no_hp'            => $data['no_hp'],
            'layanan_id'       => $data['layanan_id'],
            'jenis_sepatu'     => $data['jenis_sepatu'],
            'merek'            => $data['merek'] ?? null,
            'warna'            => $data['warna'] ?? null,
            'kondisi'          => $data['kondisi'] ?? null,
            'jumlah_pasang'    => $data['jumlah_pasang'],
            'harga_satuan'     => $hargaSatuan,
            'catatan'          => $data['catatan'] ?? null,
            'catatan_lokasi'   => $data['catatan_lokasi'] ?? null,
            'voucher_id'       => $voucherId,
            'diskon'           => $diskon,
            'estimasi_selesai' => $data['estimasi_selesai'],
            'status'           => 'diterima',
            'hpp'              => $data['hpp'],
        ]);

        Pembayaran::create([
            'order_id'     => $order->id,
            'total'        => $total - $diskon,
            'metode'       => $data['metode_bayar'],
            'status'       => in_array($data['metode_bayar'], ['lunas', 'cash', 'qris']) ? 'lunas' : 'belum',
            'dibayar_pada' => in_array($data['metode_bayar'], ['lunas', 'cash', 'qris']) ? now() : null,
        ]);

        $order->load('layanan', 'pembayaran', 'lokasi');
        KirimWaJob::dispatch($order->no_hp, $this->wa->pesanOrderMasuk($order));

        $redirect = redirect()->route('orders.show', $order)
            ->with('success', "Order {$order->no_order} berhasil dibuat.");

        if ($voucherWarning) {
            $redirect = $redirect->with('warning', $voucherWarning);
        }

        return $redirect;
    }

    public function show(Order $order)
    {
        $order->load(['layanan', 'pembayaran', 'user', 'pelanggan', 'lokasi.layanans', 'voucher', 'review']);
        $lokasis = Lokasi::where('aktif', true)->orderBy('kode')->get();
        return view('orders.show', compact('order', 'lokasis'));
    }

    public function edit(Order $order)
    {
        if ($order->isSudahSelesai()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Order selesai tidak bisa diedit.');
        }
        $layanans   = Layanan::where('aktif', true)->get();
        $lokasis    = Lokasi::where('aktif', true)->orderBy('kode')->get();
        $hppLayanan = HppLayanan::select('layanan_id', DB::raw('sum(biaya) as total_hpp'))
            ->groupBy('layanan_id')->pluck('total_hpp', 'layanan_id');

        return view('orders.edit', compact('order', 'layanans', 'lokasis', 'hppLayanan'));
    }

    public function update(Request $request, Order $order)
    {
        if ($order->isSudahSelesai()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Order selesai tidak bisa diedit.');
        }

        $data = $request->validate([
            'nama_pelanggan'   => 'required|string|max:100',
            'no_hp'            => 'required|string|max:20',
            'layanan_id'       => 'required|exists:layanans,id',
            'jenis_sepatu'     => 'required|string|max:50',
            'merek'            => 'nullable|string|max:50',
            'warna'            => 'nullable|string|max:30',
            'kondisi'          => 'nullable|string|max:100',
            'jumlah_pasang'    => 'required|integer|min:1|max:20',
            'catatan'          => 'nullable|string|max:500',
            'estimasi_selesai' => 'required|date',
            'lokasi_id'        => 'nullable|exists:lokasis,id',
            'catatan_lokasi'   => 'nullable|string|max:200',
        ]);

        $layanan     = Layanan::findOrFail($data['layanan_id']);
        $hargaSatuan = $layanan->harga;
        $total       = $hargaSatuan * $data['jumlah_pasang'];
        if (!empty($data['lokasi_id'])) {
            $lokasi = Lokasi::with('layanans')->find($data['lokasi_id']);
            if ($lokasi) {
                $hargaSatuan = $lokasi->hitungHarga($layanan->harga, $layanan->id);
                $total       = $hargaSatuan * $data['jumlah_pasang'];
            }
        }

        $oldLayananId = $order->layanan_id;
        $oldJumlah    = $order->jumlah_pasang;

        $data['harga_satuan'] = $hargaSatuan;
        $order->update($data);
        $order->pembayaran?->update(['total' => $total - $order->diskon]);

        if ($data['layanan_id'] != $oldLayananId || $data['jumlah_pasang'] != $oldJumlah) {
            $totalHpp = HppLayanan::where('layanan_id', $data['layanan_id'])->sum('biaya');
            $order->update(['hpp' => $totalHpp * $data['jumlah_pasang']]);
        }

        return redirect()->route('orders.show', $order)
            ->with('success', "Order {$order->no_order} berhasil diperbarui.");
    }

    public function updateStatus(Request $request, Order $order)
    {
        $allStatuses = array_merge(Order::STATUSES, Order::LEGACY_STATUSES);
        $request->validate(['status' => 'required|in:' . implode(',', $allStatuses)]);

        $updateData = ['status' => $request->status];

        // Tandai selesai_pada saat siap diambil
        if ($request->status === 'siap_diambil' && !$order->selesai_pada) {
            $updateData['selesai_pada'] = now();
        }

        $order->update($updateData);

        // Kirim notif WA sesuai tahap (async via queue)
        if ($request->status === 'dicuci') {
            $order->load('layanan', 'pembayaran', 'lokasi');
            KirimWaJob::dispatch($order->no_hp, $this->wa->pesanMulaiDicuci($order));
        }

        if ($request->status === 'siap_diambil') {
            $order->load('layanan', 'pembayaran', 'pelanggan', 'lokasi');
            KirimWaJob::dispatch($order->no_hp, $this->wa->pesanOrderSelesai($order));

            if ($order->pelanggan && $order->poin > 0) {
                $order->pelanggan->tambahPoin(
                    $order->poin,
                    "Order {$order->no_order} — {$order->layanan->nama}",
                    $order->id
                );
                $order->pelanggan->updateTier();
            }
        }

        if ($request->status === 'selesai') {
            if ($order->pembayaran && $order->pembayaran->status !== 'lunas') {
                $order->pembayaran->update(['status' => 'lunas', 'dibayar_pada' => now()]);
            }
        }

        return back()->with('success', "Status diperbarui ke " . ucfirst(str_replace('_', ' ', $request->status)) . ".");
    }

    public function tandaiLunas(Order $order)
    {
        if (!$order->pembayaran) {
            return back()->with('error', 'Data pembayaran tidak ditemukan.');
        }
        if ($order->pembayaran->status === 'lunas') {
            return back()->with('error', 'Pembayaran sudah lunas.');
        }
        $order->pembayaran->update([
            'status'       => 'lunas',
            'dibayar_pada' => now(),
        ]);
        return back()->with('success', "Pembayaran order {$order->no_order} ditandai lunas.");
    }

    public function updateLokasi(Request $request, Order $order)
    {
        $data = $request->validate([
            'lokasi_id'      => 'nullable|exists:lokasis,id',
            'catatan_lokasi' => 'nullable|string|max:200',
        ]);
        $order->update($data);
        $lokasiNama = $order->lokasi?->nama ?? 'Tanpa lokasi';
        return back()->with('success', "Lokasi diperbarui ke {$lokasiNama}.");
    }

    public function kirimUlangWa(Order $order)
    {
        $order->load('layanan', 'pembayaran', 'lokasi', 'pelanggan');
        $pesan    = $order->status === 'siap_diambil'
            ? $this->wa->pesanOrderSelesai($order)
            : $this->wa->pesanOrderMasuk($order);
        $terkirim = $this->wa->kirim($order->no_hp, $pesan);
        return back()->with(
            $terkirim ? 'success' : 'error',
            $terkirim ? "WA terkirim ke {$order->no_hp}." : "Gagal kirim WA."
        );
    }

    public function kirimInvoice(Order $order)
    {
        $order->load('layanan', 'pembayaran', 'user');
        $terkirim = $this->wa->kirimInvoiceLink($order);
        return back()->with(
            $terkirim ? 'success' : 'error',
            $terkirim ? "Invoice WA terkirim ke {$order->no_hp}." : "Gagal kirim invoice WA."
        );
    }

    public function cetakNota(Order $order)
    {
        $order->load('layanan', 'pembayaran', 'user', 'voucher');
        $pdf = Pdf::loadView('pdf.nota', compact('order'))
            ->setPaper([0, 0, 226.77, 600], 'portrait');
        return $pdf->stream("nota-{$order->no_order}.pdf");
    }
}

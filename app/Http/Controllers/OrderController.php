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
    public function __construct(
        protected WhatsappService $wa,
        protected \App\Services\StockAutomationService $stockAutomation
    ) {}

    public function index(Request $request)
    {
        $orders = Order::with(['items.layanan', 'pembayaran', 'lokasi', 'pelanggan'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->cari, function ($q) use ($request) {
                $cari = $request->cari;
                $normalized = \App\Http\Middleware\NormalizePhoneNumber::normalize($cari);
                $q->where(function ($q) use ($cari, $normalized) {
                    $q->where('nama_pelanggan', 'like', "%{$cari}%")
                      ->orWhere('no_order', 'like', "%{$cari}%")
                      ->orWhere('no_hp', 'like', "%{$cari}%");
                    if ($normalized) {
                        $q->orWhere('no_hp', 'like', "%{$normalized}%");
                    }
                });
            })
            ->latest()->paginate(15)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $layanans   = Layanan::where('aktif', true)->orderBy('nama')->get();
        $jenisBarangs = \App\Models\JenisBarang::where('aktif', true)->orderBy('nama')->get();
        $lokasis    = Lokasi::where('aktif', true)->orderBy('kode')->get();

        return view('orders.create', compact('layanans', 'jenisBarangs', 'lokasis'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_pelanggan'   => 'required|string|max:100',
            'no_hp'            => 'required|string|max:20',
            'catatan'          => 'nullable|string|max:500',
            'estimasi_selesai' => 'required|date_format:Y-m-d\TH:i',
            'metode_bayar'     => 'required|in:tempo,transfer,lunas,cash,qris',
            'lokasi_id'        => 'nullable|exists:lokasis,id',
            'catatan_lokasi'   => 'nullable|string|max:200',
            'voucher_kode'     => 'nullable|string|max:30',
            'tukar_poin'       => 'nullable|boolean',
            'items'            => 'required|array|min:1',
            'items.*.layanan_id' => 'required|exists:layanans,id',
            'items.*.jenis_barang_id' => 'required|exists:jenis_barangs,id',
            'items.*.jumlah_pasang' => 'required|integer|min:1|max:20',
            'items.*.merek'    => 'nullable|string|max:50',
            'items.*.warna'    => 'nullable|string|max:30',
            'items.*.kondisi'  => 'nullable|string|max:100',
        ]);

        $pelanggan = Pelanggan::firstOrCreate(
            ['no_hp' => $data['no_hp']],
            ['nama'  => $data['nama_pelanggan']]
        );
        if ($pelanggan->nama !== $data['nama_pelanggan']) {
            $pelanggan->update(['nama' => $data['nama_pelanggan']]);
        }

        $order = DB::transaction(function () use ($data, $pelanggan) {
            $order = Order::create([
                'user_id'          => auth()->id(),
                'pelanggan_id'     => $pelanggan->id,
                'lokasi_id'        => $data['lokasi_id'] ?? null,
                'nama_pelanggan'   => $data['nama_pelanggan'],
                'no_hp'            => $data['no_hp'],
                'catatan'          => $data['catatan'] ?? null,
                'catatan_lokasi'   => $data['catatan_lokasi'] ?? null,
                'estimasi_selesai' => $data['estimasi_selesai'],
                'status'           => 'draft',
            ]);

            $lokasi = ($data['lokasi_id'] ?? null) ? Lokasi::find($data['lokasi_id']) : null;
            $grandTotal = 0;

            foreach ($data['items'] as $itemData) {
                $layanan = Layanan::findOrFail($itemData['layanan_id']);
                $hargaSatuan = $layanan->harga;
                if ($lokasi) {
                    $hargaSatuan = $lokasi->hitungHarga($layanan->harga, $layanan->id);
                }
                $hargaTotal = $hargaSatuan * $itemData['jumlah_pasang'];
                $itemHpp = $layanan->total_hpp * $itemData['jumlah_pasang'];
                $grossProfit = $hargaTotal - $itemHpp;
                $grossMargin = $hargaTotal > 0 ? round(($grossProfit / $hargaTotal) * 100, 2) : 0;

                $order->items()->create([
                    'layanan_id' => $itemData['layanan_id'],
                    'jenis_barang_id' => $itemData['jenis_barang_id'],
                    'jumlah_pasang' => $itemData['jumlah_pasang'],
                    'harga_satuan' => $hargaSatuan,
                    'merek' => $itemData['merek'] ?? null,
                    'warna' => $itemData['warna'] ?? null,
                    'kondisi' => $itemData['kondisi'] ?? null,
                    'hpp' => $itemHpp,
                    'gross_profit' => $grossProfit,
                    'gross_margin' => $grossMargin,
                ]);

                $grandTotal += $hargaTotal;
            }

            // Terapkan voucher
            $voucherId = null;
            $diskon    = 0;
            if (!empty($data['voucher_kode'])) {
                $voucher = Voucher::where('kode', strtoupper($data['voucher_kode']))->first();
                if ($voucher && $voucher->masihBerlaku($grandTotal)) {
                    $diskon    = $voucher->hitungDiskon($grandTotal);
                    $voucherId = $voucher->id;
                    $voucher->increment('terpakai');
                }
            }

            // Terapkan poin redemption
            $poinDigunakan = 0;
            $diskonPoin    = 0;
            if (!empty($data['tukar_poin']) && $pelanggan->poin > 0) {
                $nilaiPoin  = $pelanggan->nilaiPoin();
                $diskonPoin = min($nilaiPoin, $grandTotal - $diskon); // cannot exceed remaining total
                $poinDigunakan = (int) ceil($diskonPoin / 100);       // back to poin units
                $diskonPoin    = $poinDigunakan * 100;                 // recalculate exact value
            }

            $totalHpp = $order->items()->sum('hpp');
            $totalPasang = $order->items()->sum('jumlah_pasang');
            $firstItem = $order->items()->first();

            $order->update([
                'voucher_id'    => $voucherId,
                'diskon'        => $diskon,
                'poin_digunakan'=> $poinDigunakan,
                'diskon_poin'   => $diskonPoin,
                'jumlah_pasang' => $totalPasang,
                'hpp'           => $totalHpp,
                'layanan_id'    => $firstItem?->layanan_id,
                'jenis_sepatu'  => $firstItem?->jenisBarang?->nama,
            ]);

            Pembayaran::create([
                'order_id'     => $order->id,
                'total'        => max(0, $grandTotal - $diskon - $diskonPoin),
                'metode'       => $data['metode_bayar'],
                'status'       => in_array($data['metode_bayar'], ['lunas', 'cash', 'qris']) ? 'selesai' : 'belum_selesai',
                'dibayar_pada' => in_array($data['metode_bayar'], ['lunas', 'cash', 'qris']) ? now() : null,
            ]);

            // Kurangi poin pelanggan jika digunakan
            if ($poinDigunakan > 0) {
                $pelanggan->tukarPoin($poinDigunakan, "Ditukar saat order {$order->no_order}");
            }

            return $order;
        });

        $order->load(['items.layanan', 'pembayaran', 'lokasi']);
        $warnings = $this->stockAutomation->deductStock($order);
        KirimWaJob::dispatch($order->no_hp, $this->wa->pesanOrderMasuk($order));

        $redirect = redirect()->route('orders.show', $order)
            ->with('success', "Order {$order->no_order} berhasil dibuat.");

        if (!empty($warnings)) {
            $redirect = $redirect->with('warning', implode(' ', $warnings));
        }

        return $redirect;
    }

    public function show(Order $order)
    {
        $order->load(['items.layanan', 'items.jenisBarang', 'pembayaran', 'user', 'pelanggan', 'lokasi.layanans', 'voucher', 'review']);
        $lokasis = Lokasi::where('aktif', true)->orderBy('kode')->get();
        return view('orders.show', compact('order', 'lokasis'));
    }

    public function edit(Order $order)
    {
        if ($order->isSudahSelesai()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Order selesai tidak bisa diedit.');
        }
        $order->load('items');
        $layanans   = Layanan::where('aktif', true)->orderBy('nama')->get();
        $jenisBarangs = \App\Models\JenisBarang::where('aktif', true)->orderBy('nama')->get();
        $lokasis    = Lokasi::where('aktif', true)->orderBy('kode')->get();

        return view('orders.edit', compact('order', 'layanans', 'jenisBarangs', 'lokasis'));
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
            'catatan'          => 'nullable|string|max:500',
            'estimasi_selesai' => 'required|date_format:Y-m-d\TH:i',
            'lokasi_id'        => 'nullable|exists:lokasis,id',
            'catatan_lokasi'   => 'nullable|string|max:200',
            'items'            => 'required|array|min:1',
            'items.*.id'       => 'nullable|exists:order_items,id',
            'items.*.layanan_id' => 'required|exists:layanans,id',
            'items.*.jenis_barang_id' => 'required|exists:jenis_barangs,id',
            'items.*.jumlah_pasang' => 'required|integer|min:1|max:20',
            'items.*.merek'    => 'nullable|string|max:50',
            'items.*.warna'    => 'nullable|string|max:30',
            'items.*.kondisi'  => 'nullable|string|max:100',
            'tukar_poin'       => 'nullable|boolean',
        ]);

        $pelanggan = Pelanggan::firstOrCreate(
            ['no_hp' => $data['no_hp']],
            ['nama'  => $data['nama_pelanggan']]
        );
        if ($pelanggan->nama !== $data['nama_pelanggan']) {
            $pelanggan->update(['nama' => $data['nama_pelanggan']]);
        }

        DB::transaction(function () use ($order, $data, $pelanggan) {
            if ($order->status !== 'batal') {
                $this->stockAutomation->reverseStock($order);
            }

            // Kembalikan poin yang sebelumnya digunakan (jika ada)
            if (($order->poin_digunakan ?? 0) > 0) {
                $pelanggan->tambahPoin(
                    $order->poin_digunakan,
                    "Refund poin karena order {$order->no_order} diedit",
                    $order->id
                );
                $order->update(['poin_digunakan' => 0, 'diskon_poin' => 0]);
                $pelanggan->refresh();
            }

            $order->update([
                'pelanggan_id'     => $pelanggan->id,
                'nama_pelanggan'   => $data['nama_pelanggan'],
                'no_hp'            => $data['no_hp'],
                'catatan'          => $data['catatan'] ?? null,
                'estimasi_selesai' => $data['estimasi_selesai'],
                'lokasi_id'        => $data['lokasi_id'] ?? null,
                'catatan_lokasi'   => $data['catatan_lokasi'] ?? null,
            ]);

            $lokasi = $data['lokasi_id'] ? Lokasi::find($data['lokasi_id']) : null;
            $keptIds = [];
            $grandTotal = 0;

            foreach ($data['items'] as $itemData) {
                $layanan = Layanan::findOrFail($itemData['layanan_id']);
                $hargaSatuan = $layanan->harga;
                if ($lokasi) {
                    $hargaSatuan = $lokasi->hitungHarga($layanan->harga, $layanan->id);
                }
                $hargaTotal = $hargaSatuan * $itemData['jumlah_pasang'];
                $itemHpp = $layanan->total_hpp * $itemData['jumlah_pasang'];
                $grossProfit = $hargaTotal - $itemHpp;
                $grossMargin = $hargaTotal > 0 ? round(($grossProfit / $hargaTotal) * 100, 2) : 0;

                $itemFields = [
                    'layanan_id' => $itemData['layanan_id'],
                    'jenis_barang_id' => $itemData['jenis_barang_id'],
                    'jumlah_pasang' => $itemData['jumlah_pasang'],
                    'harga_satuan' => $hargaSatuan,
                    'merek' => $itemData['merek'] ?? null,
                    'warna' => $itemData['warna'] ?? null,
                    'kondisi' => $itemData['kondisi'] ?? null,
                    'hpp' => $itemHpp,
                    'gross_profit' => $grossProfit,
                    'gross_margin' => $grossMargin,
                ];

                if (!empty($itemData['id'])) {
                    $orderItem = $order->items()->findOrFail($itemData['id']);
                    $orderItem->update($itemFields);
                    $keptIds[] = $orderItem->id;
                } else {
                    $orderItem = $order->items()->create($itemFields);
                    $keptIds[] = $orderItem->id;
                }

                $grandTotal += $hargaTotal;
            }

            // Hapus item yang dibuang
            $order->items()->whereNotIn('id', $keptIds)->delete();

            // Hitung ulang diskon
            $diskon = 0;
            if ($order->voucher) {
                if ($order->voucher->masihBerlaku($grandTotal)) {
                    $diskon = $order->voucher->hitungDiskon($grandTotal);
                } else {
                    $order->update(['voucher_id' => null]);
                }
            }
            $totalHpp = $order->items()->sum('hpp');
            $totalPasang = $order->items()->sum('jumlah_pasang');
            $firstItem = $order->items()->first();

            // Terapkan poin redemption baru
            $poinDigunakan = 0;
            $diskonPoin    = 0;
            if (!empty($data['tukar_poin']) && $pelanggan->poin > 0) {
                $nilaiPoin  = $pelanggan->nilaiPoin();
                $diskonPoin = min($nilaiPoin, $grandTotal - $diskon);
                $poinDigunakan = (int) ceil($diskonPoin / 100);
                $diskonPoin    = $poinDigunakan * 100;
            }

            $order->update([
                'diskon'         => $diskon,
                'poin_digunakan' => $poinDigunakan,
                'diskon_poin'    => $diskonPoin,
                'jumlah_pasang'  => $totalPasang,
                'hpp'            => $totalHpp,
                'layanan_id'     => $firstItem?->layanan_id,
                'jenis_sepatu'   => $firstItem?->jenisBarang?->nama,
            ]);

            $order->pembayaran?->update([
                'total' => max(0, $grandTotal - $diskon - $diskonPoin)
            ]);

            $order->load('items.layanan');

            if ($order->status !== 'batal') {
                $this->stockAutomation->deductStock($order);
            }

            // Kurangi poin baru jika digunakan
            if ($poinDigunakan > 0) {
                $pelanggan->tukarPoin($poinDigunakan, "Ditukar saat edit order {$order->no_order}");
            }
        });

        return redirect()->route('orders.show', $order)
            ->with('success', "Order {$order->no_order} berhasil diperbarui.");
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|in:' . implode(',', Order::STATUSES)]);

        $oldStatus = $order->status;
        $updateData = ['status' => $request->status];

        if ($request->status === 'siap_diambil' && !$order->selesai_pada) {
            $updateData['selesai_pada'] = now();
        }

        $order->update($updateData);

        // Tambah poin dan tier pelanggan
        if ($request->status === 'siap_diambil') {
            $order->load('items.layanan', 'pembayaran', 'pelanggan');
            if ($order->pelanggan && $order->poin > 0) {
                $order->pelanggan->tambahPoin(
                    $order->poin,
                    "Order {$order->no_order}",
                    $order->id
                );
                $order->pelanggan->updateTier();
            }
        }

        // Dispatch WA status updates
        if ($request->status === 'diproses' && $oldStatus !== 'diproses') {
            KirimWaJob::dispatch($order->no_hp, $this->wa->pesanMulaiDicuci($order));
        } elseif ($request->status === 'siap_diambil' && $oldStatus !== 'siap_diambil') {
            KirimWaJob::dispatch($order->no_hp, $this->wa->pesanOrderSelesai($order));
        }

        if ($request->status === 'selesai') {
            if ($order->pembayaran && $order->pembayaran->status !== 'selesai') {
                $order->pembayaran->update(['status' => 'selesai', 'dibayar_pada' => now()]);
            }
        }

        // Reverse stock and refund poin if order is cancelled
        if ($request->status === 'batal' && $oldStatus !== 'batal') {
            $this->stockAutomation->reverseStock($order);

            // Kembalikan poin yang sudah ditukar saat order ini dibuat
            if ($order->poin_digunakan > 0) {
                $order->load('pelanggan');
                if ($order->pelanggan) {
                    $order->pelanggan->tambahPoin(
                        $order->poin_digunakan,
                        "Refund poin dari pembatalan order {$order->no_order}",
                        $order->id
                    );
                }
            }
        }

        return back()->with('success', "Status diperbarui ke " . ucfirst(str_replace('_', ' ', $request->status)) . ".");
    }

    public function tandaiLunas(Order $order)
    {
        if (!$order->pembayaran) {
            return back()->with('error', 'Data pembayaran tidak ditemukan.');
        }
        if ($order->pembayaran->status === 'selesai') {
            return back()->with('error', 'Pembayaran sudah lunas.');
        }
        $order->pembayaran->update([
            'status'       => 'selesai',
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
        $order->load('items.layanan', 'pembayaran', 'lokasi', 'pelanggan');
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
        $order->load('items.layanan', 'pembayaran', 'user');
        $terkirim = $this->wa->kirimInvoiceLink($order);
        return back()->with(
            $terkirim ? 'success' : 'error',
            $terkirim ? "Invoice WA terkirim ke {$order->no_hp}." : "Gagal kirim invoice WA."
        );
    }

    public function cetakNota(Order $order)
    {
        $order->load('items.layanan', 'items.jenisBarang', 'pembayaran', 'user', 'voucher');
        $pdf = Pdf::loadView('pdf.nota', compact('order'))
            ->setPaper([0, 0, 226.77, 600], 'portrait');
        return $pdf->stream("nota-{$order->no_order}.pdf");
    }
}

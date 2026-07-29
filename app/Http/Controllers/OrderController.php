<?php

namespace App\Http\Controllers;

use App\Http\Middleware\NormalizePhoneNumber;
use App\Jobs\KirimWaJob;
use App\Models\JenisBarang;
use App\Models\Layanan;
use App\Models\Lokasi;
use App\Models\Order;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Voucher;
use App\Services\StockAutomationService;
use App\Services\WhatsappService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(
        protected WhatsappService $wa,
        protected StockAutomationService $stockAutomation
    ) {}

    public function index(Request $request)
    {
        $orders = Order::with(['items.layanan', 'pembayaran', 'lokasi', 'pelanggan'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->cari, function ($q) use ($request) {
                $cari = $request->cari;
                $normalized = NormalizePhoneNumber::normalize($cari);
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
        $layanans = Layanan::where('aktif', true)->orderBy('nama')->get();
        $jenisBarangs = JenisBarang::where('aktif', true)->orderBy('nama')->get();
        $lokasis = Lokasi::where('aktif', true)->orderBy('kode')->get();

        return view('orders.create', compact('layanans', 'jenisBarangs', 'lokasis'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'idempotency_key' => 'nullable|uuid',
            'nama_pelanggan' => 'required|string|max:100',
            'no_hp' => ['required', 'regex:/^62[0-9]{8,13}$/'],
            'catatan' => 'nullable|string|max:500',
            'estimasi_selesai' => 'required|date_format:Y-m-d\TH:i',
            'metode_bayar' => 'required|in:tempo,transfer,lunas,cash,qris',
            'lokasi_id' => 'nullable|exists:lokasis,id',
            'catatan_lokasi' => 'nullable|string|max:200',
            'voucher_kode' => 'nullable|string|max:30',
            'tukar_poin' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.layanan_id' => 'required|exists:layanans,id',
            'items.*.jenis_barang_id' => 'required|exists:jenis_barangs,id',
            'items.*.jumlah_pasang' => 'required|integer|min:1|max:20',
            'items.*.merek' => 'nullable|string|max:50',
            'items.*.warna' => 'nullable|string|max:30',
            'items.*.kondisi' => 'nullable|string|max:100',
        ]);
        $data['idempotency_key'] ??= (string) Str::uuid();

        if ($existing = Order::where('idempotency_key', $data['idempotency_key'])->first()) {
            return redirect()->route('orders.show', $existing)
                ->with('success', "Order {$existing->no_order} sudah tersimpan.");
        }

        $warnings = [];
        $created = false;
        try {
            $order = DB::transaction(function () use ($data, &$warnings) {
                $now = now();
                Pelanggan::upsert([[
                    'nama' => $data['nama_pelanggan'],
                    'no_hp' => $data['no_hp'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]], ['no_hp'], ['nama', 'updated_at']);
                $pelanggan = Pelanggan::where('no_hp', $data['no_hp'])->lockForUpdate()->firstOrFail();

                $order = Order::create([
                    'idempotency_key' => $data['idempotency_key'],
                    'user_id' => auth()->id(),
                    'pelanggan_id' => $pelanggan->id,
                    'lokasi_id' => $data['lokasi_id'] ?? null,
                    'nama_pelanggan' => $data['nama_pelanggan'],
                    'no_hp' => $data['no_hp'],
                    'catatan' => $data['catatan'] ?? null,
                    'catatan_lokasi' => $data['catatan_lokasi'] ?? null,
                    'estimasi_selesai' => $data['estimasi_selesai'],
                    'status' => 'draft',
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
                $diskon = 0;
                if (! empty($data['voucher_kode'])) {
                    $voucher = Voucher::where('kode', strtoupper($data['voucher_kode']))
                        ->lockForUpdate()
                        ->first();
                    if ($voucher && $voucher->masihBerlaku($grandTotal)) {
                        $diskon = $voucher->hitungDiskon($grandTotal);
                        $voucherId = $voucher->id;
                        $voucher->increment('terpakai');
                    }
                }

                // Terapkan poin redemption
                $poinDigunakan = 0;
                $diskonPoin = 0;
                if (! empty($data['tukar_poin']) && $pelanggan->poin > 0) {
                    $poinDigunakan = min(
                        (int) $pelanggan->poin,
                        intdiv(max(0, $grandTotal - $diskon), 100)
                    );
                    $diskonPoin = $poinDigunakan * 100;
                }

                $totalHpp = $order->items()->sum('hpp');
                $totalPasang = $order->items()->sum('jumlah_pasang');
                $firstItem = $order->items()->first();

                $order->update([
                    'voucher_id' => $voucherId,
                    'diskon' => $diskon,
                    'poin_digunakan' => $poinDigunakan,
                    'diskon_poin' => $diskonPoin,
                    'jumlah_pasang' => $totalPasang,
                    'hpp' => $totalHpp,
                    'layanan_id' => $firstItem?->layanan_id,
                    'jenis_sepatu' => $firstItem?->jenisBarang?->nama,
                ]);

                Pembayaran::create([
                    'order_id' => $order->id,
                    'total' => max(0, $grandTotal - $diskon - $diskonPoin),
                    'metode' => $data['metode_bayar'],
                    'status' => in_array($data['metode_bayar'], ['lunas', 'cash', 'qris']) ? 'selesai' : 'belum_selesai',
                    'dibayar_pada' => in_array($data['metode_bayar'], ['lunas', 'cash', 'qris']) ? now() : null,
                ]);

                // Kurangi poin pelanggan jika digunakan
                if ($poinDigunakan > 0) {
                    if (! $pelanggan->tukarPoin(
                        $poinDigunakan,
                        "Ditukar saat order {$order->no_order}",
                        $order->id,
                        "order:{$order->id}:redeem"
                    )) {
                        throw ValidationException::withMessages([
                            'tukar_poin' => 'Saldo poin berubah. Silakan muat ulang dan coba lagi.',
                        ]);
                    }
                }

                $order->load(['items.layanan.recipes.bahan.stok', 'pembayaran', 'lokasi']);
                $warnings = $this->stockAutomation->deductStock($order);

                return $order;
            }, 3);
            $created = true;
        } catch (QueryException $exception) {
            $order = Order::where('idempotency_key', $data['idempotency_key'])->first();
            if (! $order) {
                throw $exception;
            }
        }

        $order->load(['items.layanan', 'pembayaran', 'lokasi']);
        if ($created) {
            KirimWaJob::dispatch($order->no_hp, $this->wa->pesanOrderMasuk($order));
        }

        $redirect = redirect()->route('orders.show', $order)
            ->with('success', $created
                ? "Order {$order->no_order} berhasil dibuat."
                : "Order {$order->no_order} sudah tersimpan.");

        if (! empty($warnings)) {
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
        $layanans = Layanan::where('aktif', true)->orderBy('nama')->get();
        $jenisBarangs = JenisBarang::where('aktif', true)->orderBy('nama')->get();
        $lokasis = Lokasi::where('aktif', true)->orderBy('kode')->get();

        return view('orders.edit', compact('order', 'layanans', 'jenisBarangs', 'lokasis'));
    }

    public function update(Request $request, Order $order)
    {
        if ($order->isSudahSelesai()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Order selesai tidak bisa diedit.');
        }

        $data = $request->validate([
            'nama_pelanggan' => 'required|string|max:100',
            'no_hp' => ['required', 'regex:/^62[0-9]{8,13}$/'],
            'catatan' => 'nullable|string|max:500',
            'estimasi_selesai' => 'required|date_format:Y-m-d\TH:i',
            'lokasi_id' => 'nullable|exists:lokasis,id',
            'catatan_lokasi' => 'nullable|string|max:200',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:order_items,id',
            'items.*.layanan_id' => 'required|exists:layanans,id',
            'items.*.jenis_barang_id' => 'required|exists:jenis_barangs,id',
            'items.*.jumlah_pasang' => 'required|integer|min:1|max:20',
            'items.*.merek' => 'nullable|string|max:50',
            'items.*.warna' => 'nullable|string|max:30',
            'items.*.kondisi' => 'nullable|string|max:100',
            'tukar_poin' => 'nullable|boolean',
        ]);

        $pelanggan = Pelanggan::firstOrCreate(
            ['no_hp' => $data['no_hp']],
            ['nama' => $data['nama_pelanggan']]
        );
        if ($pelanggan->nama !== $data['nama_pelanggan']) {
            $pelanggan->update(['nama' => $data['nama_pelanggan']]);
        }

        $warnings = [];
        DB::transaction(function () use ($order, $data, $pelanggan, &$warnings) {
            $order = Order::lockForUpdate()->findOrFail($order->id);
            $originalCustomer = $order->pelanggan_id
                ? Pelanggan::lockForUpdate()->find($order->pelanggan_id)
                : null;
            $pelanggan = Pelanggan::lockForUpdate()->findOrFail($pelanggan->id);
            $this->stockAutomation->reverseStock($order);

            if (($order->poin_digunakan ?? 0) > 0 && $originalCustomer) {
                $originalCustomer->tambahPoin(
                    $order->poin_digunakan,
                    "Refund poin karena order {$order->no_order} diedit",
                    $order->id
                );
                $order->update(['poin_digunakan' => 0, 'diskon_poin' => 0]);
                $pelanggan->refresh();
            }

            $order->update([
                'pelanggan_id' => $pelanggan->id,
                'nama_pelanggan' => $data['nama_pelanggan'],
                'no_hp' => $data['no_hp'],
                'catatan' => $data['catatan'] ?? null,
                'estimasi_selesai' => $data['estimasi_selesai'],
                'lokasi_id' => $data['lokasi_id'] ?? null,
                'catatan_lokasi' => $data['catatan_lokasi'] ?? null,
            ]);

            $lokasi = ($data['lokasi_id'] ?? null) ? Lokasi::find($data['lokasi_id']) : null;
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

                if (! empty($itemData['id'])) {
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

            $diskon = 0;
            if ($order->voucher) {
                $voucher = Voucher::lockForUpdate()->find($order->voucher_id);
                if ($voucher && $grandTotal >= $voucher->min_transaksi) {
                    $diskon = $voucher->hitungDiskon($grandTotal);
                } else {
                    $voucher?->decrement('terpakai', min(1, $voucher->terpakai));
                    $order->update(['voucher_id' => null]);
                }
            }
            $totalHpp = $order->items()->sum('hpp');
            $totalPasang = $order->items()->sum('jumlah_pasang');
            $firstItem = $order->items()->first();

            // Terapkan poin redemption baru
            $poinDigunakan = 0;
            $diskonPoin = 0;
            if (! empty($data['tukar_poin']) && $pelanggan->poin > 0) {
                $poinDigunakan = min(
                    (int) $pelanggan->poin,
                    intdiv(max(0, $grandTotal - $diskon), 100)
                );
                $diskonPoin = $poinDigunakan * 100;
            }

            $order->update([
                'diskon' => $diskon,
                'poin_digunakan' => $poinDigunakan,
                'diskon_poin' => $diskonPoin,
                'jumlah_pasang' => $totalPasang,
                'hpp' => $totalHpp,
                'layanan_id' => $firstItem?->layanan_id,
                'jenis_sepatu' => $firstItem?->jenisBarang?->nama,
            ]);

            $order->pembayaran?->update([
                'total' => max(0, $grandTotal - $diskon - $diskonPoin),
            ]);

            $order->load('items.layanan.recipes.bahan.stok');
            $warnings = $this->stockAutomation->deductStock($order);

            if ($poinDigunakan > 0 && ! $pelanggan->tukarPoin(
                $poinDigunakan,
                "Ditukar saat edit order {$order->no_order}",
                $order->id
            )) {
                throw ValidationException::withMessages([
                    'tukar_poin' => 'Saldo poin berubah. Silakan muat ulang dan coba lagi.',
                ]);
            }
        }, 3);

        $redirect = redirect()->route('orders.show', $order)
            ->with('success', "Order {$order->no_order} berhasil diperbarui.");

        return $warnings
            ? $redirect->with('warning', implode(' ', $warnings))
            : $redirect;
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate(['status' => 'required|in:'.implode(',', Order::STATUSES)]);

        $oldStatus = DB::transaction(function () use ($order, $data) {
            $order = Order::with(['pembayaran', 'pelanggan'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if (! $order->canTransitionTo($data['status'])) {
                throw ValidationException::withMessages([
                    'status' => "Transisi {$order->status} ke {$data['status']} tidak diizinkan.",
                ]);
            }

            $oldStatus = $order->status;
            $update = ['status' => $data['status']];
            if ($data['status'] === 'siap_diambil' && ! $order->selesai_pada) {
                $update['selesai_pada'] = now();
            }
            $order->update($update);

            if ($data['status'] === 'selesai') {
                if ($order->pembayaran && $order->pembayaran->status !== 'selesai') {
                    $order->pembayaran->update(['status' => 'selesai', 'dibayar_pada' => now()]);
                }
                $order->load('pembayaran');
                if ($order->pelanggan && ! $order->poin_diberikan_pada && $order->poin > 0) {
                    $order->pelanggan->tambahPoin(
                        $order->poin,
                        "Order {$order->no_order}",
                        $order->id,
                        "order:{$order->id}:award"
                    );
                    $order->pelanggan->updateTier();
                    $order->update(['poin_diberikan_pada' => now()]);
                }
            }

            if ($data['status'] === 'batal') {
                $this->stockAutomation->reverseStock($order);

                if ($order->poin_digunakan > 0 && $order->pelanggan) {
                    $order->pelanggan->tambahPoin(
                        $order->poin_digunakan,
                        "Refund poin dari pembatalan order {$order->no_order}",
                        $order->id,
                        "order:{$order->id}:cancel-refund"
                    );
                    $order->update(['poin_digunakan' => 0, 'diskon_poin' => 0]);
                }

                if ($order->voucher_id && ! $order->voucher_dikembalikan_pada) {
                    $voucher = Voucher::lockForUpdate()->find($order->voucher_id);
                    $voucher?->decrement('terpakai', min(1, $voucher->terpakai));
                    $order->update(['voucher_dikembalikan_pada' => now()]);
                }
            }

            return $oldStatus;
        }, 3);

        $order->refresh();
        if ($data['status'] === 'diproses' && $oldStatus !== 'diproses') {
            KirimWaJob::dispatch($order->no_hp, $this->wa->pesanMulaiDicuci($order));
        } elseif ($data['status'] === 'siap_diambil' && $oldStatus !== 'siap_diambil') {
            KirimWaJob::dispatch($order->no_hp, $this->wa->pesanOrderSelesai($order));
        }

        return back()->with('success', 'Status diperbarui ke '.ucfirst(str_replace('_', ' ', $data['status'])).'.');
    }

    public function tandaiLunas(Order $order)
    {
        if (! $order->pembayaran) {
            return back()->with('error', 'Data pembayaran tidak ditemukan.');
        }
        if ($order->pembayaran->status === 'selesai') {
            return back()->with('error', 'Pembayaran sudah lunas.');
        }
        $order->pembayaran->update([
            'status' => 'selesai',
            'dibayar_pada' => now(),
        ]);

        return back()->with('success', "Pembayaran order {$order->no_order} ditandai lunas.");
    }

    public function updateLokasi(Request $request, Order $order)
    {
        $data = $request->validate([
            'lokasi_id' => 'nullable|exists:lokasis,id',
            'catatan_lokasi' => 'nullable|string|max:200',
        ]);
        $order->update($data);
        $lokasiNama = $order->lokasi?->nama ?? 'Tanpa lokasi';

        return back()->with('success', "Lokasi diperbarui ke {$lokasiNama}.");
    }

    public function kirimUlangWa(Order $order)
    {
        $order->load('items.layanan', 'pembayaran', 'lokasi', 'pelanggan');
        $pesan = $order->status === 'siap_diambil'
            ? $this->wa->pesanOrderSelesai($order)
            : $this->wa->pesanOrderMasuk($order);
        $terkirim = $this->wa->kirim($order->no_hp, $pesan);

        return back()->with(
            $terkirim ? 'success' : 'error',
            $terkirim ? "WA terkirim ke {$order->no_hp}." : 'Gagal kirim WA.'
        );
    }

    public function kirimInvoice(Order $order)
    {
        $order->load('items.layanan', 'pembayaran', 'user');
        $terkirim = $this->wa->kirimInvoiceLink($order);

        return back()->with(
            $terkirim ? 'success' : 'error',
            $terkirim ? "Invoice WA terkirim ke {$order->no_hp}." : 'Gagal kirim invoice WA.'
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

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Pembayaran;
use App\Models\Stok;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Status "masih dikerjakan" — lama + baru
    const STATUS_AKTIF = ['antri', 'proses', 'diterima', 'inspeksi', 'dicuci', 'kering', 'finishing'];
    // Status "sudah selesai/diambil"
    const STATUS_DONE  = ['diambil', 'selesai'];
    // Status yang dihitung dalam pendapatan (tidak antri/diterima saja)
    const STATUS_HITUNG = ['proses', 'selesai', 'diambil', 'inspeksi', 'dicuci', 'kering', 'finishing', 'siap_diambil'];

    public function index()
    {
        if (auth()->user()->isCleaner()) {
            $aktif = Order::with(['layanan', 'lokasi.layanans'])
                ->whereIn('status', self::STATUS_AKTIF)
                ->latest()->get();
            $terlambat = $aktif->filter(fn($o) => $o->estimasi_selesai?->isPast())->count();
            return view('dashboard-cleaner', compact('aktif', 'terlambat'));
        }

        // ── Stats hari ini ──────────────────────────────────────────────────
        $stats = [
            'pendapatan_hari_ini' => Pembayaran::whereDate('dibayar_pada', today())
                ->where('status', 'lunas')->sum('total'),
            'hpp_hari_ini'        => Order::whereDate('created_at', today())
                ->whereIn('status', self::STATUS_HITUNG)->sum('hpp'),
            'gross_profit_hari'   => (int) DB::table('orders')
                ->leftJoin('pembayarans', 'pembayarans.order_id', '=', 'orders.id')
                ->join('layanans', 'layanans.id', '=', 'orders.layanan_id')
                ->whereDate('orders.created_at', today())
                ->whereIn('orders.status', self::STATUS_HITUNG)
                ->selectRaw('COALESCE(SUM(COALESCE(pembayarans.total, layanans.harga * orders.jumlah_pasang) - orders.hpp), 0) as profit')
                ->value('profit'),
            'order_hari_ini'      => Order::whereDate('created_at', today())->count(),
            'dalam_antrian'       => Order::whereIn('status', self::STATUS_AKTIF)->count(),
            'siap_diambil'        => Order::where('status', 'siap_diambil')->count(),
        ];

        $stats['gross_margin'] = $stats['pendapatan_hari_ini'] > 0
            ? round(($stats['gross_profit_hari'] / $stats['pendapatan_hari_ini']) * 100, 1)
            : 0;

        // ── Stats bulan ini ─────────────────────────────────────────────────
        $ordersBulanIni = Order::with(['layanan', 'pembayaran', 'lokasi.layanans'])
            ->select('id', 'layanan_id', 'lokasi_id', 'jumlah_pasang', 'harga_satuan', 'diskon', 'hpp')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereIn('status', self::STATUS_HITUNG)
            ->get();

        $statsBulan = [
            'gross_sales'  => $ordersBulanIni->sum('gross_sales'),
            'net_sales'    => Pembayaran::whereYear('dibayar_pada', now()->year)
                ->whereMonth('dibayar_pada', now()->month)
                ->where('status', 'lunas')->sum('total'),
            'hpp'          => $ordersBulanIni->sum('hpp'),
            'gross_profit' => $ordersBulanIni->sum('gross_profit'),
            'transactions' => $ordersBulanIni->count(),
        ];
        $statsBulan['gross_margin'] = $statsBulan['net_sales'] > 0
            ? round(($statsBulan['gross_profit'] / $statsBulan['net_sales']) * 100, 2)
            : 0;
        $statsBulan['avg_per_transaksi'] = $statsBulan['transactions'] > 0
            ? intdiv($statsBulan['net_sales'], $statsBulan['transactions'])
            : 0;
        $statsBulan['diskon'] = $statsBulan['gross_sales'] - $statsBulan['net_sales'];

        // ── Order terbaru ───────────────────────────────────────────────────
        $orders_terbaru = Order::with(['layanan', 'lokasi.layanans', 'pembayaran'])->latest()->take(8)->get();

        $terlambat = Order::whereIn('status', self::STATUS_AKTIF)
            ->where('estimasi_selesai', '<', today())->count();

        // ── Grafik harian 7 hari ────────────────────────────────────────────
        $grafikHarian = Pembayaran::where('status', 'lunas')
            ->where('dibayar_pada', '>=', now()->subDays(6)->startOfDay())
            ->select(DB::raw('DATE(dibayar_pada) as tanggal'), DB::raw('SUM(total) as total'))
            ->groupBy('tanggal')->orderBy('tanggal')->get();

        // ── Grafik bulanan 12 bulan ─────────────────────────────────────────
        $grafikPendapatan = Pembayaran::where('status', 'lunas')
            ->where('dibayar_pada', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw('YEAR(dibayar_pada) as tahun'),
                DB::raw('MONTH(dibayar_pada) as bulan'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as jumlah_order')
            )
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')->orderBy('bulan')->get();

        // ── Top layanan bulan ini ────────────────────────────────────────────
        $topItems = $ordersBulanIni
            ->groupBy('layanan_id')
            ->map(function ($items) {
                $layanan = $items->first()->layanan;
                return [
                    'nama'         => $layanan->nama,
                    'item_sold'    => $items->sum('jumlah_pasang'),
                    'gross_sales'  => $items->sum('gross_sales'),
                    'net_sales'    => $items->sum('net_sales'),
                    'gross_profit' => $items->sum('gross_profit'),
                    'margin'       => $items->sum('net_sales') > 0
                        ? round(($items->sum('gross_profit') / $items->sum('net_sales')) * 100, 1)
                        : 0,
                ];
            })
            ->sortByDesc('gross_sales')
            ->values()
            ->take(10);

        $stokMenipis = Stok::orderBy('nama')->get()
            ->filter(fn($s) => $s->status_stok !== 'aman');

        return view('dashboard', compact(
            'stats', 'statsBulan', 'orders_terbaru', 'terlambat',
            'grafikPendapatan', 'grafikHarian', 'topItems', 'stokMenipis'
        ));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Pembayaran;
use App\Models\Stok;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Status yang masih membutuhkan tindak lanjut staf.
    const STATUS_AKTIF = ['draft', 'menunggu_pembayaran', 'diproses', 'siap_diambil'];

    // Status yang dihitung dalam pendapatan
    public function index()
    {
        if (auth()->user()->isCleaner()) {
            $aktif = Order::with(['items.layanan', 'lokasi'])
                ->whereIn('status', self::STATUS_AKTIF)
                ->latest()->get();
            $terlambat = $aktif->filter(fn ($o) => $o->estimasi_selesai?->isPast())->count();

            return view('dashboard-cleaner', compact('aktif', 'terlambat'));
        }

        // ── Stats hari ini ──────────────────────────────────────────────────
        $todayOrders = Order::with(['items', 'pembayaran'])
            ->where('status', '!=', 'batal')
            ->whereHas('pembayaran', fn ($query) => $query
                ->where('status', 'selesai')
                ->whereDate('dibayar_pada', today()))
            ->get();

        $stats = [
            'pendapatan_hari_ini' => $todayOrders->sum('net_sales'),
            'hpp_hari_ini' => $todayOrders->sum('hpp'),
            'gross_profit_hari' => $todayOrders->sum('gross_profit'),
            'order_hari_ini' => Order::whereDate('created_at', today())->count(),
            'dalam_antrian' => Order::whereIn('status', self::STATUS_AKTIF)->count(),
            'siap_diambil' => Order::where('status', 'siap_diambil')->count(),
        ];

        $stats['gross_margin'] = $stats['pendapatan_hari_ini'] > 0
            ? round(($stats['gross_profit_hari'] / $stats['pendapatan_hari_ini']) * 100, 1)
            : 0;

        // ── Stats bulan ini ─────────────────────────────────────────────────
        $ordersBulanIni = Order::with(['items.layanan', 'pembayaran'])
            ->where('status', '!=', 'batal')
            ->whereHas('pembayaran', fn ($query) => $query
                ->where('status', 'selesai')
                ->whereYear('dibayar_pada', now()->year)
                ->whereMonth('dibayar_pada', now()->month))
            ->get();

        $statsBulan = [
            'gross_sales' => $ordersBulanIni->sum('gross_sales'),
            'net_sales' => $ordersBulanIni->sum('net_sales'),
            'hpp' => $ordersBulanIni->sum('hpp'),
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
        $orders_terbaru = Order::with(['items.layanan', 'lokasi', 'pembayaran'])->latest()->take(8)->get();

        $terlambat = Order::whereIn('status', self::STATUS_AKTIF)
            ->where('estimasi_selesai', '<', now())->count();

        // ── Grafik harian 7 hari ────────────────────────────────────────────
        $grafikHarian = Pembayaran::where('status', 'selesai')
            ->where('dibayar_pada', '>=', now()->subDays(6)->startOfDay())
            ->select(DB::raw('DATE(dibayar_pada) as tanggal'), DB::raw('SUM(total) as total'))
            ->groupBy('tanggal')->orderBy('tanggal')->get();

        // ── Grafik bulanan 12 bulan ─────────────────────────────────────────
        $driver = DB::getDriverName();
        $yearExpression = $driver === 'sqlite' ? "strftime('%Y', dibayar_pada)" : 'YEAR(dibayar_pada)';
        $monthExpression = $driver === 'sqlite' ? "CAST(strftime('%m', dibayar_pada) as integer)" : 'MONTH(dibayar_pada)';

        $grafikPendapatan = Pembayaran::where('status', 'selesai')
            ->where('dibayar_pada', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw("$yearExpression as tahun"),
                DB::raw("$monthExpression as bulan"),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as jumlah_order')
            )
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')->orderBy('bulan')->get();

        // ── Top layanan bulan ini (dari order_items untuk akurasi) ──────────
        // Group by layanan via items (multi-item aware)
        $allItems = $ordersBulanIni->flatMap(fn ($o) => $o->items);
        $topItems = $allItems
            ->groupBy('layanan_id')
            ->map(function ($items) {
                $layanan = $items->first()->layanan;
                if (! $layanan) {
                    return null;
                }
                $totalHarga = $items->sum(fn ($i) => $i->harga_satuan * $i->jumlah_pasang);
                $netSales = $items->sum('net_sales');
                $totalHpp = $items->sum('hpp');
                $profit = $netSales - $totalHpp;

                return [
                    'nama' => $layanan->nama,
                    'item_sold' => $items->sum('jumlah_pasang'),
                    'gross_sales' => $totalHarga,
                    'net_sales' => $netSales,
                    'gross_profit' => $profit,
                    'margin' => $netSales > 0
                        ? round(($profit / $netSales) * 100, 1)
                        : 0,
                ];
            })
            ->filter() // buang null (layanan dihapus)
            ->sortByDesc('gross_sales')
            ->values()
            ->take(10);

        $stokMenipis = Stok::with('bahan')->get()
            ->filter(fn ($s) => $s->bahan && $s->bahan->aktif && $s->status_stok !== 'aman')
            ->sortBy(fn ($s) => $s->nama);

        return view('dashboard', compact(
            'stats', 'statsBulan', 'orders_terbaru', 'terlambat',
            'grafikPendapatan', 'grafikHarian', 'topItems', 'stokMenipis'
        ));
    }
}

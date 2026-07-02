<?php

namespace App\Http\Controllers;

use App\Models\HppLayanan;
use App\Models\Layanan;
use App\Models\Order;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HppController extends Controller
{
    public function index()
    {
        $layanans = Layanan::with('hppKomponen')->orderBy('nama')->get();
        return view('hpp.index', compact('layanans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'layanan_id' => 'required|exists:layanans,id',
            'komponen'   => 'required|string|max:100',
            'biaya'      => 'required|integer|min:0',
        ]);

        HppLayanan::create($data);
        return back()->with('success', "Komponen HPP berhasil ditambahkan.");
    }

    public function update(Request $request, HppLayanan $hpp)
    {
        $data = $request->validate([
            'komponen' => 'required|string|max:100',
            'biaya'    => 'required|integer|min:0',
        ]);

        $hpp->update($data);
        return back()->with('success', "HPP berhasil diperbarui.");
    }

    public function destroy(HppLayanan $hpp)
    {
        $hpp->delete();
        return back()->with('success', "Komponen HPP dihapus.");
    }

    // Laporan profit/loss
    public function laporan(Request $request)
    {
        $bulan = $request->validate(['bulan' => 'nullable|date_format:Y-m'])['bulan'] ?? now()->format('Y-m');
        [$tahun, $bln] = explode('-', $bulan);

        $orders = Order::with(['layanan', 'pembayaran', 'lokasi'])
            ->whereYear('orders.created_at', $tahun)
            ->whereMonth('orders.created_at', $bln)
            ->where('status', '!=', 'antri')
            ->latest()->get();

        // Hitung agregat
        $grossSales  = $orders->sum('gross_sales');
        $netSales    = $orders->sum('net_sales');
        $totalHpp    = $orders->sum('hpp');
        $grossProfit = $orders->sum('gross_profit');
        $diskon      = $grossSales - $netSales;
        $grossMargin = $netSales > 0 ? round(($grossProfit / $netSales) * 100, 2) : 0;

        // Rekap per layanan (seperti foto item summary)
        $rekapLayanan = $orders->groupBy('layanan_id')->map(function ($items) {
            $layanan     = $items->first()->layanan;
            $grossSales  = $items->sum('gross_sales');
            $netSales    = $items->sum('net_sales');
            $hpp         = $items->sum('hpp');
            $grossProfit = $items->sum('gross_profit');
            return [
                'nama'         => $layanan->nama,
                'item_sold'    => $items->sum('jumlah_pasang'),
                'gross_sales'  => $grossSales,
                'net_sales'    => $netSales,
                'hpp'          => $hpp,
                'gross_profit' => $grossProfit,
                'margin'       => $netSales > 0 ? round(($grossProfit / $netSales) * 100, 1) : 0,
            ];
        })->sortByDesc('gross_sales')->values();

        // Rekap per lokasi
        $rekapLokasi = $orders->whereNotNull('lokasi_id')
            ->groupBy('lokasi_id')->map(function ($items) {
                $lokasi = $items->first()->lokasi;
                return [
                    'nama'         => $lokasi?->nama ?? 'Tanpa lokasi',
                    'kode'         => $lokasi?->kode ?? '-',
                    'jumlah_order' => $items->count(),
                    'net_sales'    => $items->sum('net_sales'),
                    'hpp'          => $items->sum('hpp'),
                    'gross_profit' => $items->sum('gross_profit'),
                ];
            })->sortByDesc('net_sales')->values();

        return view('hpp.laporan', compact(
            'bulan', 'orders',
            'grossSales', 'netSales', 'totalHpp', 'grossProfit', 'diskon', 'grossMargin',
            'rekapLayanan', 'rekapLokasi'
        ));
    }
}

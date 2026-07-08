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
        $layanans = Layanan::with(['recipes.bahan'])->orderBy('nama')->get();
        $bahans = \App\Models\Bahan::where('aktif', true)->orderBy('nama')->get();
        return view('hpp.index', compact('layanans', 'bahans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'layanan_id' => 'required|exists:layanans,id',
            'bahan_id'   => 'required|exists:bahans,id',
            'jumlah_penggunaan' => 'required|numeric|min:0.01',
        ]);

        \App\Models\LayananRecipe::updateOrCreate(
            ['layanan_id' => $data['layanan_id'], 'bahan_id' => $data['bahan_id']],
            ['jumlah_penggunaan' => $data['jumlah_penggunaan']]
        );

        return back()->with('success', "Bahan resep berhasil ditambahkan/diperbarui.");
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'jumlah_penggunaan' => 'required|numeric|min:0.01',
        ]);

        $recipe = \App\Models\LayananRecipe::findOrFail($id);
        $recipe->update($data);
        return back()->with('success', "Bahan resep berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $recipe = \App\Models\LayananRecipe::findOrFail($id);
        $recipe->delete();
        return back()->with('success', "Bahan resep berhasil dihapus.");
    }

    // Laporan profit/loss
    public function laporan(Request $request)
    {
        $bulan = $request->validate(['bulan' => 'nullable|date_format:Y-m'])['bulan'] ?? now()->format('Y-m');
        [$tahun, $bln] = explode('-', $bulan);

        $orders = Order::with(['items.layanan', 'pembayaran', 'lokasi'])
            ->whereYear('orders.created_at', $tahun)
            ->whereMonth('orders.created_at', $bln)
            ->where('status', '!=', 'batal')
            ->latest()->get();

        // Hitung agregat
        $grossSales  = $orders->sum('gross_sales');
        $netSales    = $orders->sum('net_sales');
        $totalHpp    = $orders->sum('hpp');
        $grossProfit = $orders->sum('gross_profit');
        $diskon      = $grossSales - $netSales;
        $grossMargin = $netSales > 0 ? round(($grossProfit / $netSales) * 100, 2) : 0;

        // Rekap per layanan dari order_items (multi-item aware)
        $allItems = $orders->flatMap(fn($o) => $o->items);
        $rekapLayanan = $allItems
            ->groupBy('layanan_id')
            ->map(function ($items) {
                $layanan     = $items->first()->layanan;
                if (!$layanan) return null;
                $grossSales  = $items->sum(fn($i) => $i->harga_satuan * $i->jumlah_pasang);
                $netSales    = $grossSales;
                $hpp         = $items->sum('hpp');
                $grossProfit = $grossSales - $hpp;
                return [
                    'nama'         => $layanan->nama,
                    'item_sold'    => $items->sum('jumlah_pasang'),
                    'gross_sales'  => $grossSales,
                    'net_sales'    => $netSales,
                    'hpp'          => $hpp,
                    'gross_profit' => $grossProfit,
                    'margin'       => $netSales > 0 ? round(($grossProfit / $netSales) * 100, 1) : 0,
                ];
            })
            ->filter()
            ->sortByDesc('gross_sales')->values();

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

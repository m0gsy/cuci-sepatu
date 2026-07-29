<?php

namespace App\Http\Controllers;

use App\Models\Operasional;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperasionalController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->validate(['bulan' => 'nullable|date_format:Y-m'])['bulan'] ?? now()->format('Y-m');
        [$tahun, $bln] = explode('-', $bulan);

        $operasionals = Operasional::with('user')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->when($request->kategori, fn ($q) => $q->where('kategori', $request->kategori))
            ->latest('tanggal')
            ->paginate(20)->withQueryString();

        $totalBiaya = Operasional::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)->sum('jumlah');

        $pendapatan = Pembayaran::whereYear('pembayarans.dibayar_pada', $tahun)
            ->whereMonth('pembayarans.dibayar_pada', $bln)
            ->where('status', 'selesai')->sum('total');

        $rekapKategori = Operasional::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->select('kategori', DB::raw('sum(jumlah) as total'))
            ->groupBy('kategori')
            ->orderByDesc('total')->get();

        return view('operasional.index', compact(
            'operasionals', 'totalBiaya', 'pendapatan', 'rekapKategori', 'bulan'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:200',
            'kategori' => 'required|in:bahan,utilitas,gaji,sewa,peralatan,lainnya',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'catatan' => 'nullable|string|max:500',
        ]);
        Operasional::create(array_merge($data, ['user_id' => auth()->id()]));

        return back()->with('success', "Biaya '{$data['nama']}' berhasil dicatat.");
    }

    public function destroy(Operasional $operasional)
    {
        $nama = $operasional->nama;
        $operasional->delete();

        return back()->with('success', "Biaya '{$nama}' berhasil dihapus.");
    }
}

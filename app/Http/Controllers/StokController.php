<?php

namespace App\Http\Controllers;

use App\Models\Stok;
use App\Models\StokMutasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    public function index()
    {
        $stoks = Stok::with('bahan')->get()->filter(fn ($s) => $s->bahan && $s->bahan->aktif)->sortBy(fn ($s) => $s->nama);
        $menipis = $stoks->filter(fn ($s) => $s->status_stok !== 'aman');

        return view('stok.index', compact('stoks', 'menipis'));
    }

    // Direct Stok creation is disabled; stocks are created automatically when Bahans are added.
    public function update(Request $request, Stok $stok)
    {
        $data = $request->validate([
            'stok_minimum' => 'required|numeric|min:0',
            'catatan' => 'nullable|string|max:300',
        ]);
        $stok->update($data);

        return back()->with('success', "Konfigurasi stok '{$stok->nama}' berhasil diperbarui.");
    }

    public function mutasi(Request $request, Stok $stok)
    {
        $data = $request->validate([
            'tipe' => 'required|in:masuk,keluar,penyesuaian',
            'jumlah' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string|max:200',
        ]);

        DB::transaction(function () use ($stok, $data) {
            $stok = Stok::lockForUpdate()->findOrFail($stok->id);

            $sebelum = $stok->stok_saat_ini;
            $sesudah = match ($data['tipe']) {
                'masuk' => $sebelum + $data['jumlah'],
                'keluar' => max(0, $sebelum - $data['jumlah']),
                'penyesuaian' => $data['jumlah'],
            };

            $stok->update(['stok_saat_ini' => $sesudah]);
            $jumlahAktual = $data['tipe'] === 'keluar'
                ? $sebelum - $sesudah
                : ($data['tipe'] === 'penyesuaian' ? abs($sesudah - $sebelum) : $data['jumlah']);

            StokMutasi::create([
                'stok_id' => $stok->id,
                'user_id' => auth()->id(),
                'tipe' => $data['tipe'],
                'jumlah' => $jumlahAktual,
                'stok_sebelum' => $sebelum,
                'stok_sesudah' => $sesudah,
                'keterangan' => $data['keterangan'] ?? null,
            ]);
        });

        return back()->with('success', "Stok '{$stok->nama}' berhasil diperbarui.");
    }

    public function riwayat(Stok $stok)
    {
        $mutasis = $stok->mutasis()->with('user')->latest()->paginate(20);

        return view('stok.riwayat', compact('stok', 'mutasis'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Stok;
use App\Models\StokMutasi;
use Illuminate\Http\Request;

class StokController extends Controller
{
    public function index()
    {
        $stoks   = Stok::orderBy('nama')->get();
        $menipis = $stoks->filter(fn($s) => $s->status_stok !== 'aman');
        return view('stok.index', compact('stoks', 'menipis'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'          => 'required|string|max:100|unique:stoks,nama',
            'satuan'        => 'required|string|max:30',
            'stok_saat_ini' => 'required|numeric|min:0',
            'stok_minimum'  => 'required|numeric|min:0',
            'harga_satuan'  => 'required|integer|min:0',
            'catatan'       => 'nullable|string|max:300',
        ]);
        Stok::create($data);
        return back()->with('success', "Bahan '{$data['nama']}' berhasil ditambahkan.");
    }

    public function update(Request $request, Stok $stok)
    {
        $data = $request->validate([
            'nama'         => 'required|string|max:100|unique:stoks,nama,' . $stok->id,
            'satuan'       => 'required|string|max:30',
            'stok_minimum' => 'required|numeric|min:0',
            'harga_satuan' => 'required|integer|min:0',
            'catatan'      => 'nullable|string|max:300',
        ]);
        $stok->update($data);
        return back()->with('success', "Data '{$stok->nama}' berhasil diperbarui.");
    }

    public function mutasi(Request $request, Stok $stok)
    {
        $data = $request->validate([
            'tipe'       => 'required|in:masuk,keluar,penyesuaian',
            'jumlah'     => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string|max:200',
        ]);

        $sebelum = $stok->stok_saat_ini;

        $sesudah = match($data['tipe']) {
            'masuk'       => $sebelum + $data['jumlah'],
            'keluar'      => max(0, $sebelum - $data['jumlah']),
            'penyesuaian' => $data['jumlah'],
        };

        $stok->update(['stok_saat_ini' => $sesudah]);

        StokMutasi::create([
            'stok_id'      => $stok->id,
            'user_id'      => auth()->id(),
            'tipe'         => $data['tipe'],
            'jumlah'       => $data['jumlah'],
            'stok_sebelum' => $sebelum,
            'stok_sesudah' => $sesudah,
            'keterangan'   => $data['keterangan'] ?? null,
        ]);

        return back()->with('success', "Stok '{$stok->nama}' berhasil diperbarui.");
    }

    public function riwayat(Stok $stok)
    {
        $mutasis = $stok->mutasis()->with('user')->latest()->paginate(20);
        return view('stok.riwayat', compact('stok', 'mutasis'));
    }
}

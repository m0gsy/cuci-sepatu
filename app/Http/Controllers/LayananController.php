<?php

namespace App\Http\Controllers;

use App\Models\KategoriLayanan;
use App\Models\Layanan;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    public function index()
    {
        $layanans = Layanan::with(['kategoriLayanan', 'recipes.bahan'])->orderBy('nama')->get();
        $categories = KategoriLayanan::where('aktif', true)->orderBy('nama')->get();

        return view('layanans.index', compact('layanans', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kategori_layanan_id' => 'required|exists:kategori_layanans,id',
            'nama' => 'required|string|max:100|unique:layanans,nama',
            'harga' => 'required|integer|min:1000',
            'estimasi_nilai' => 'required|integer|min:1',
            'estimasi_satuan' => 'required|string|in:Hari,Jam',
        ]);
        Layanan::create($data);

        return back()->with('success', "Layanan {$data['nama']} berhasil ditambahkan.");
    }

    public function update(Request $request, Layanan $layanan)
    {
        $data = $request->validate([
            'kategori_layanan_id' => 'required|exists:kategori_layanans,id',
            'nama' => 'required|string|max:100|unique:layanans,nama,'.$layanan->id,
            'harga' => 'required|integer|min:1000',
            'estimasi_nilai' => 'required|integer|min:1',
            'estimasi_satuan' => 'required|string|in:Hari,Jam',
        ]);
        $layanan->update($data);

        return back()->with('success', 'Layanan berhasil diperbarui.');
    }

    public function toggleAktif(Layanan $layanan)
    {
        $layanan->update(['aktif' => ! $layanan->aktif]);
        $status = $layanan->aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Layanan {$layanan->nama} {$status}.");
    }
}

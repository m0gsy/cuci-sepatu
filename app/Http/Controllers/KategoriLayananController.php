<?php

namespace App\Http\Controllers;

use App\Models\KategoriLayanan;
use Illuminate\Http\Request;

class KategoriLayananController extends Controller
{
    public function index()
    {
        $categories = KategoriLayanan::orderBy('nama')->get();

        return view('kategori-layanans.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:kategori_layanans,nama',
        ]);
        KategoriLayanan::create([
            'nama' => $data['nama'],
            'aktif' => true,
        ]);

        return back()->with('success', "Kategori '{$data['nama']}' berhasil ditambahkan.");
    }

    public function update(Request $request, KategoriLayanan $kategoriLayanan)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:kategori_layanans,nama,'.$kategoriLayanan->id,
        ]);
        $kategoriLayanan->update(['nama' => $data['nama']]);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function toggle(KategoriLayanan $kategoriLayanan)
    {
        $kategoriLayanan->update(['aktif' => ! $kategoriLayanan->aktif]);
        $status = $kategoriLayanan->aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kategori '{$kategoriLayanan->nama}' berhasil {$status}.");
    }
}

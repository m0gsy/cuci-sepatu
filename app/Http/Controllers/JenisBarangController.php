<?php

namespace App\Http\Controllers;

use App\Models\JenisBarang;
use Illuminate\Http\Request;

class JenisBarangController extends Controller
{
    public function index()
    {
        $itemTypes = JenisBarang::orderBy('nama')->get();

        return view('jenis-barangs.index', compact('itemTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:jenis_barangs,nama',
        ]);
        JenisBarang::create([
            'nama' => $data['nama'],
            'aktif' => true,
        ]);

        return back()->with('success', "Jenis barang '{$data['nama']}' berhasil ditambahkan.");
    }

    public function update(Request $request, JenisBarang $jenisBarang)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:jenis_barangs,nama,'.$jenisBarang->id,
        ]);
        $jenisBarang->update(['nama' => $data['nama']]);

        return back()->with('success', 'Jenis barang berhasil diperbarui.');
    }

    public function toggle(JenisBarang $jenisBarang)
    {
        $jenisBarang->update(['aktif' => ! $jenisBarang->aktif]);
        $status = $jenisBarang->aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Jenis barang '{$jenisBarang->nama}' berhasil {$status}.");
    }
}

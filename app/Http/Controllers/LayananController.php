<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    public function index()
    {
        $layanans = Layanan::with('hppKomponen')->latest()->get();
        return view('layanans.index', compact('layanans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'          => 'required|string|max:100|unique:layanans,nama',
            'harga'         => 'required|integer|min:1000',
            'estimasi_hari' => 'required|integer|min:1|max:30',
        ]);
        Layanan::create($data);
        return back()->with('success', "Layanan {$data['nama']} berhasil ditambahkan.");
    }

    public function update(Request $request, Layanan $layanan)
    {
        $data = $request->validate([
            'nama'          => 'required|string|max:100|unique:layanans,nama,' . $layanan->id,
            'harga'         => 'required|integer|min:1000',
            'estimasi_hari' => 'required|integer|min:1|max:30',
        ]);
        $layanan->update($data);
        return back()->with('success', "Layanan berhasil diperbarui.");
    }

    public function toggleAktif(Layanan $layanan)
    {
        $layanan->update(['aktif' => !$layanan->aktif]);
        $status = $layanan->aktif ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Layanan {$layanan->nama} {$status}.");
    }
}

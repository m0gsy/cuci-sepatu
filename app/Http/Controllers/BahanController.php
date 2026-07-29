<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use App\Models\Stok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BahanController extends Controller
{
    public function index()
    {
        $bahans = Bahan::with('stok')->orderBy('nama')->get();

        return view('bahans.index', compact('bahans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:bahans,nama',
            'satuan' => 'required|string|max:30',
            'harga_beli' => 'required|integer|min:0',
            'isi_kemasan' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($data) {
            $bahan = Bahan::create([
                'nama' => $data['nama'],
                'satuan' => $data['satuan'],
                'harga_beli' => $data['harga_beli'],
                'isi_kemasan' => $data['isi_kemasan'],
                'aktif' => true,
            ]);

            // Create corresponding Stok record
            Stok::create([
                'bahan_id' => $bahan->id,
                'stok_saat_ini' => 0.00,
                'stok_minimum' => 0.00,
                'catatan' => 'Inisialisasi bahan baku baru',
            ]);
        });

        return back()->with('success', "Bahan baku '{$data['nama']}' berhasil ditambahkan.");
    }

    public function update(Request $request, Bahan $bahan)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:bahans,nama,'.$bahan->id,
            'satuan' => 'required|string|max:30',
            'harga_beli' => 'required|integer|min:0',
            'isi_kemasan' => 'required|integer|min:1',
        ]);

        $bahan->update($data);

        return back()->with('success', 'Bahan baku berhasil diperbarui.');
    }

    public function toggle(Bahan $bahan)
    {
        $bahan->update(['aktif' => ! $bahan->aktif]);
        $status = $bahan->aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Bahan baku '{$bahan->nama}' berhasil {$status}.");
    }
}

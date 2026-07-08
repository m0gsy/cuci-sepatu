<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use App\Models\Layanan;
use App\Models\Order;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    public function index()
    {
        $lokasis = Lokasi::with('layanans')
            ->withCount([
                'orders as order_aktif'   => fn($q) => $q->whereIn('status', ['draft', 'menunggu_pembayaran', 'diproses', 'siap_diambil']),
                'orders as order_selesai' => fn($q) => $q->whereIn('status', ['selesai']),
            ])
            ->orderBy('kode')->get();

        $orderAktif = Order::with(['layanan', 'lokasi'])
            ->whereIn('status', ['draft', 'menunggu_pembayaran', 'diproses', 'siap_diambil'])
            ->whereNotNull('lokasi_id')
            ->latest()->get();

        $layanans = Layanan::where('aktif', true)->orderBy('nama')->get();

        return view('lokasi.index', compact('lokasis', 'orderAktif', 'layanans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode'           => 'required|string|max:20|unique:lokasis,kode',
            'nama'           => 'required|string|max:100',
            'deskripsi'      => 'nullable|string|max:300',
            'harga_custom'   => 'boolean',
            'harga_tambahan' => 'nullable|integer',
        ]);

        $data['harga_custom']   = $request->boolean('harga_custom');
        $data['harga_tambahan'] = $data['harga_custom'] ? ($data['harga_tambahan'] ?? 0) : 0;

        Lokasi::create($data);
        return back()->with('success', "Lokasi '{$data['nama']}' berhasil ditambahkan.");
    }

    public function update(Request $request, Lokasi $lokasi)
    {
        $data = $request->validate([
            'kode'           => 'required|string|max:20|unique:lokasis,kode,' . $lokasi->id,
            'nama'           => 'required|string|max:100',
            'deskripsi'      => 'nullable|string|max:300',
            'harga_custom'   => 'boolean',
            'harga_tambahan' => 'nullable|integer',
        ]);

        $data['harga_custom']   = $request->boolean('harga_custom');
        $data['harga_tambahan'] = $data['harga_custom'] ? ($data['harga_tambahan'] ?? 0) : 0;

        $lokasi->update($data);
        return back()->with('success', "Lokasi berhasil diperbarui.");
    }

    public function toggleAktif(Lokasi $lokasi)
    {
        $lokasi->update(['aktif' => !$lokasi->aktif]);
        return back()->with('success', "Lokasi '{$lokasi->nama}' " . ($lokasi->aktif ? 'diaktifkan' : 'dinonaktifkan') . ".");
    }

    // Set harga spesifik untuk satu layanan di lokasi ini
    public function setHargaLayanan(Request $request, Lokasi $lokasi)
    {
        $data = $request->validate([
            'layanan_id' => 'required|exists:layanans,id',
            'harga'      => 'required|integer|min:0',
        ]);

        $lokasi->layanans()->syncWithoutDetaching([
            $data['layanan_id'] => ['harga' => $data['harga']],
        ]);

        $layanan = Layanan::find($data['layanan_id']);
        return back()->with('success', "Harga {$layanan->nama} di {$lokasi->nama} diset ke Rp " . number_format($data['harga'], 0, ',', '.') . ".");
    }

    // Hapus override harga (kembali ke harga standar layanan)
    public function hapusHargaLayanan(Lokasi $lokasi, Layanan $layanan)
    {
        $lokasi->layanans()->detach($layanan->id);
        return back()->with('success', "Harga {$layanan->nama} di {$lokasi->nama} kembali ke harga standar.");
    }

    // API: dipakai form order saat pilih lokasi
    // Mengembalikan harga per layanan agar JS bisa hitung otomatis
    public function harga(Lokasi $lokasi, Request $request)
    {
        $lokasi->load('layanans');
        $hargaLayanan = (int) $request->harga_layanan;
        $layananId    = (int) $request->layanan_id;

        // Map: layanan_id => harga override
        $hargaPerLayanan = $lokasi->layanans->pluck('pivot.harga', 'id');

        // Harga efektif untuk layanan yang sedang dipilih
        $hargaEfektif = $hargaLayanan;
        if ($layananId && $hargaPerLayanan->has($layananId)) {
            $hargaEfektif = $hargaPerLayanan[$layananId];
        } elseif ($lokasi->harga_custom) {
            $hargaEfektif = $hargaLayanan + $lokasi->harga_tambahan;
        }

        return response()->json([
            'harga_efektif'     => $hargaEfektif,
            'harga_tambahan'    => $lokasi->harga_tambahan,
            'harga_custom'      => $lokasi->harga_custom,
            'label'             => $lokasi->harga_tambahan_format,
            'harga_per_layanan' => $hargaPerLayanan,
        ]);
    }
}

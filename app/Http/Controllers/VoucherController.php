<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::withCount('orders')->latest()->get();

        return view('vouchers.index', compact('vouchers'));
    }

    public function store(Request $request)
    {
        $request->merge(['kode' => strtoupper(trim((string) $request->kode))]);
        $data = $request->validate([
            'kode' => 'required|string|max:30|unique:vouchers,kode',
            'tipe' => 'required|in:persen,nominal',
            'nilai' => ['required', 'integer', 'min:1', Rule::when($request->tipe === 'persen', 'max:100')],
            'expired_at' => 'nullable|date|after:yesterday',
            'min_transaksi' => 'nullable|integer|min:0',
            'kuota' => 'nullable|integer|min:1',
            'deskripsi' => 'nullable|string|max:200',
        ]);
        $data['min_transaksi'] ??= 0;
        Voucher::create($data);

        return back()->with('success', "Voucher {$data['kode']} berhasil ditambahkan.");
    }

    public function update(Request $request, Voucher $voucher)
    {
        $data = $request->validate([
            'tipe' => 'required|in:persen,nominal',
            'nilai' => ['required', 'integer', 'min:1', Rule::when($request->tipe === 'persen', 'max:100')],
            'expired_at' => 'nullable|date',
            'min_transaksi' => 'nullable|integer|min:0',
            'kuota' => 'nullable|integer|min:1',
            'deskripsi' => 'nullable|string|max:200',
        ]);
        $data['min_transaksi'] ??= 0;
        $voucher->update($data);

        return back()->with('success', "Voucher {$voucher->kode} diperbarui.");
    }

    public function toggleAktif(Voucher $voucher)
    {
        $voucher->update(['aktif' => ! $voucher->aktif]);
        $label = $voucher->aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Voucher {$voucher->kode} {$label}.");
    }

    public function destroy(Voucher $voucher)
    {
        if ($voucher->orders()->exists()) {
            return back()->with('error', 'Voucher sudah digunakan, tidak bisa dihapus.');
        }
        $voucher->delete();

        return back()->with('success', 'Voucher dihapus.');
    }

    // API: cek voucher dari form order
    public function cek(Request $request)
    {
        $request->validate(['kode' => 'required', 'total' => 'required|integer|min:0']);
        $voucher = Voucher::where('kode', strtoupper($request->kode))->first();

        if (! $voucher || ! $voucher->masihBerlaku((int) $request->total)) {
            return response()->json([
                'valid' => false,
                'pesan' => 'Voucher tidak valid, sudah expired, atau tidak memenuhi minimal transaksi.',
            ]);
        }

        $diskon = $voucher->hitungDiskon((int) $request->total);

        return response()->json([
            'valid' => true,
            'voucher_id' => $voucher->id,
            'diskon' => $diskon,
            'keterangan' => "Diskon {$voucher->nilai_format} terapkan: Rp ".number_format($diskon, 0, ',', '.'),
        ]);
    }
}

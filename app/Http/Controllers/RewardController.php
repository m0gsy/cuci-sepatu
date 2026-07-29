<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\PoinHistory;
use App\Models\Reward;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function index()
    {
        $rewards = Reward::orderBy('poin_dibutuhkan')->get();

        return view('rewards.index', compact('rewards'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:rewards,nama',
            'poin_dibutuhkan' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string|max:300',
        ]);

        Reward::create(array_merge($data, ['aktif' => true]));

        return back()->with('success', "Reward '{$data['nama']}' berhasil ditambahkan.");
    }

    public function update(Request $request, Reward $reward)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:rewards,nama,'.$reward->id,
            'poin_dibutuhkan' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string|max:300',
        ]);

        $reward->update($data);

        return back()->with('success', 'Reward berhasil diperbarui.');
    }

    public function toggleAktif(Reward $reward)
    {
        $reward->update(['aktif' => ! $reward->aktif]);
        $status = $reward->aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Reward '{$reward->nama}' {$status}.");
    }

    public function destroy(Reward $reward)
    {
        $reward->delete();

        return back()->with('success', 'Reward berhasil dihapus.');
    }

    // Kelola poin pelanggan manual
    public function kelolaPoin()
    {
        $pelanggans = Pelanggan::orderBy('nama')->get(['id', 'nama', 'no_hp', 'poin']);
        $histories = PoinHistory::with('pelanggan')
            ->latest()->take(20)->get();

        return view('rewards.poin', compact('pelanggans', 'histories'));
    }

    public function tambahPoinManual(Request $request)
    {
        $data = $request->validate([
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'tipe' => 'required|in:tambah,kurang',
            'poin' => 'required|integer|min:1',
            'keterangan' => 'required|string|max:200',
        ]);

        $pelanggan = Pelanggan::findOrFail($data['pelanggan_id']);

        if ($data['tipe'] === 'tambah') {
            $pelanggan->tambahPoin($data['poin'], $data['keterangan']);

            return back()->with('success', "+{$data['poin']} poin ditambahkan ke {$pelanggan->nama}.");
        } else {
            if ($pelanggan->poin < $data['poin']) {
                return back()->with('error', "Poin {$pelanggan->nama} tidak cukup. Saat ini: {$pelanggan->poin} poin.");
            }
            $pelanggan->tukarPoin($data['poin'], $data['keterangan']);

            return back()->with('success', "-{$data['poin']} poin dikurangi dari {$pelanggan->nama}.");
        }
    }
}

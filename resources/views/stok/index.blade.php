@extends('layouts.app')
@section('title', 'Stok bahan baku')

@section('header-actions')
<button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
        class="bg-brand text-white text-xs font-medium px-4 py-2 rounded-lg hover:bg-brand-hover transition-colors">
    + Tambah bahan
</button>
@endsection

@section('content')
<div class="space-y-5">

@if($menipis->count() > 0)
<div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3.5">
    <p class="text-sm font-medium text-amber-800 mb-1">⚠ {{ $menipis->count() }} bahan perlu restok</p>
    <p class="text-xs text-amber-700">{{ $menipis->pluck('nama')->join(', ') }}</p>
</div>
@endif

<div class="space-y-3">
@forelse($stoks as $s)
<div class="bg-white border border-gray-100 rounded-xl p-5" x-data="{ edit: false, mutasi: false }">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <p class="text-sm font-semibold text-gray-900">{{ $s->nama }}</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $s->status_badge }}">
                        {{ ucfirst($s->status_stok) }}
                    </span>
                </div>
                <p class="text-xs text-gray-500">Min. {{ $s->stok_minimum }} {{ $s->satuan }} · Rp {{ number_format($s->harga_satuan, 0, ',', '.') }} / {{ $s->satuan }}</p>
            </div>
        </div>

        <div class="flex items-center gap-6 text-center">
            <div>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($s->stok_saat_ini, 1) }}</p>
                <p class="text-xs text-gray-400">{{ $s->satuan }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Rp {{ number_format($s->nilai_stok, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400">nilai stok</p>
            </div>
        </div>

        <div class="flex items-center gap-2" x-show="!edit && !mutasi">
            <a href="{{ route('stok.riwayat', $s) }}"
               class="text-xs text-gray-400 hover:text-gray-700 border border-gray-200 px-3 py-1.5 rounded-lg">
                Riwayat
            </a>
            <button @click="mutasi = true"
                    class="text-xs text-gray-400 hover:text-gray-700 border border-gray-200 px-3 py-1.5 rounded-lg">
                Catat
            </button>
            <button @click="edit = true"
                    class="text-xs text-gray-400 hover:text-gray-700 border border-gray-200 px-3 py-1.5 rounded-lg">
                Edit
            </button>
        </div>
    </div>

    {{-- Form mutasi stok --}}
    <div x-show="mutasi" class="mt-4 pt-4 border-t border-gray-100">
        <form method="POST" action="{{ route('stok.mutasi', $s) }}">
            @csrf
            <div class="grid grid-cols-3 gap-3 mb-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tipe</label>
                    <select name="tipe" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="masuk">Masuk (beli/terima)</option>
                        <option value="keluar">Keluar (dipakai)</option>
                        <option value="penyesuaian">Penyesuaian stok</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Jumlah ({{ $s->satuan }})</label>
                    <input type="number" name="jumlah" step="0.01" min="0.01"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                           placeholder="0" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Keterangan</label>
                    <input type="text" name="keterangan"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                           placeholder="Opsional">
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 text-sm bg-brand text-white rounded-lg hover:bg-brand-hover">Simpan</button>
                <button type="button" @click="mutasi = false" class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
            </div>
        </form>
    </div>

    {{-- Form edit bahan --}}
    <div x-show="edit" class="mt-4 pt-4 border-t border-gray-100">
        <form method="POST" action="{{ route('stok.update', $s) }}">
            @csrf @method('PUT')
            <div class="grid grid-cols-4 gap-3 mb-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Nama</label>
                    <input type="text" name="nama" value="{{ $s->nama }}"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Satuan</label>
                    <input type="text" name="satuan" value="{{ $s->satuan }}"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Stok minimum</label>
                    <input type="number" name="stok_minimum" value="{{ $s->stok_minimum }}" step="0.01"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Harga / satuan</label>
                    <input type="number" name="harga_satuan" value="{{ $s->harga_satuan }}"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 text-sm bg-brand text-white rounded-lg hover:bg-brand-hover">Simpan</button>
                <button type="button" @click="edit = false" class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
            </div>
        </form>
    </div>
</div>
@empty
<div class="bg-white border border-gray-100 rounded-xl p-12 text-center">
    <p class="text-sm text-gray-400">Belum ada data stok bahan baku.</p>
</div>
@endforelse
</div>

</div>

{{-- Modal tambah bahan --}}
<div id="modal-tambah" class="hidden fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-gray-100 p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-sm font-semibold text-gray-900">Tambah bahan baku</h2>
            <button onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-700 text-lg leading-none">✕</button>
        </div>
        <form method="POST" action="{{ route('stok.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="col-span-2">
                    <label class="block text-xs text-gray-500 mb-1.5">Nama bahan</label>
                    <input type="text" name="nama" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Sabun cuci khusus" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Satuan</label>
                    <input type="text" name="satuan" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="liter / pcs / botol" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Stok awal</label>
                    <input type="number" name="stok_saat_ini" step="0.01" min="0" value="0"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Stok minimum</label>
                    <input type="number" name="stok_minimum" step="0.01" min="0" value="5"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Harga per satuan (Rp)</label>
                    <input type="number" name="harga_satuan" min="0" value="0"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                        class="flex-1 py-2.5 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
                <button type="submit" class="flex-1 py-2.5 text-sm bg-brand text-white rounded-lg hover:bg-brand-hover">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection

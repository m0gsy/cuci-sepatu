@extends('layouts.app')
@section('title', 'Edit Order ' . $order->no_order)

@section('header-actions')
    <a href="{{ route('orders.show', $order) }}" class="text-xs text-gray-400 hover:text-gray-700">← Kembali</a>
@endsection

@section('content')
<div class="max-w-2xl">
<div class="bg-white border border-gray-100 rounded-xl p-6">
<form method="POST" action="{{ route('orders.update', $order) }}" x-data="editForm()">
@csrf @method('PUT')

{{-- Data pelanggan --}}
<div class="mb-6">
    <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Data pelanggan</h2>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs text-gray-500 mb-1.5">Nama pelanggan <span class="text-red-400">*</span></label>
            <input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan', $order->nama_pelanggan) }}"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
            @error('nama_pelanggan')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1.5">No. HP <span class="text-red-400">*</span></label>
            <input type="text" name="no_hp" value="{{ old('no_hp', $order->no_hp) }}"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
        </div>
    </div>
</div>

{{-- Lokasi & Layanan --}}
<div class="mb-6">
    <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Lokasi & Layanan</h2>

    @if($lokasis->count() > 0)
    <div class="mb-4">
        <label class="block text-xs text-gray-500 mb-1.5">Lokasi penyimpanan</label>
        <select name="lokasi_id"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            <option value="">-- Tanpa lokasi --</option>
            @foreach($lokasis as $lok)
            <option value="{{ $lok->id }}" {{ old('lokasi_id', $order->lokasi_id) == $lok->id ? 'selected' : '' }}>
                {{ $lok->kode }} — {{ $lok->nama }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="mb-4">
        <label class="block text-xs text-gray-500 mb-1.5">Catatan lokasi</label>
        <input type="text" name="catatan_lokasi" value="{{ old('catatan_lokasi', $order->catatan_lokasi) }}"
               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
               placeholder="Baris ke-2 dari kiri">
    </div>
    @endif

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-xs text-gray-500 mb-1.5">Jenis sepatu <span class="text-red-400">*</span></label>
            <select name="jenis_sepatu"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                @foreach(['Sneakers', 'Running', 'Boots', 'Formal', 'Sandal', 'Lainnya'] as $j)
                <option value="{{ $j }}" {{ old('jenis_sepatu', $order->jenis_sepatu) === $j ? 'selected' : '' }}>{{ $j }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1.5">Jumlah pasang <span class="text-red-400">*</span></label>
            <input type="number" name="jumlah_pasang" value="{{ old('jumlah_pasang', $order->jumlah_pasang) }}"
                   min="1" max="20"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1.5">Merek</label>
            <input type="text" name="merek" value="{{ old('merek', $order->merek) }}"
                   placeholder="Nike, Adidas..."
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1.5">Warna</label>
            <input type="text" name="warna" value="{{ old('warna', $order->warna) }}"
                   placeholder="Putih, Hitam..."
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
        </div>
    </div>

    <div class="mb-4">
        <label class="block text-xs text-gray-500 mb-1.5">Kondisi awal</label>
        <input type="text" name="kondisi" value="{{ old('kondisi', $order->kondisi) }}"
               placeholder="Kotor, ada noda, sol mengelupas..."
               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
    </div>

    <div class="mb-4">
        <label class="block text-xs text-gray-500 mb-1.5">Layanan <span class="text-red-400">*</span></label>
        <select name="layanan_id"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
            <option value="">-- Pilih layanan --</option>
            @foreach($layanans as $l)
            <option value="{{ $l->id }}" {{ old('layanan_id', $order->layanan_id) == $l->id ? 'selected' : '' }}>
                {{ $l->nama }} — Rp {{ number_format($l->harga, 0, ',', '.') }} / pasang
            </option>
            @endforeach
        </select>
        @error('layanan_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-xs text-gray-500 mb-1.5">Catatan kondisi sepatu</label>
        <textarea name="catatan" rows="2"
                  class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none"
                  placeholder="Catatan tambahan...">{{ old('catatan', $order->catatan) }}</textarea>
    </div>
</div>

{{-- Estimasi --}}
<div class="mb-6">
    <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Estimasi</h2>
    <div>
        <label class="block text-xs text-gray-500 mb-1.5">Estimasi selesai <span class="text-red-400">*</span></label>
        <input type="date" name="estimasi_selesai"
               value="{{ old('estimasi_selesai', $order->estimasi_selesai->format('Y-m-d')) }}"
               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
    </div>
</div>

<div class="border-t border-gray-100 pt-5 flex gap-3 justify-end">
    <a href="{{ route('orders.show', $order) }}"
       class="px-5 py-2.5 text-sm text-gray-500 hover:text-gray-900">Batal</a>
    <button type="submit"
            class="bg-brand text-white text-sm font-medium px-6 py-2.5 rounded-lg hover:bg-brand-hover">
        Simpan perubahan
    </button>
</div>

</form>
</div>
</div>

<script>
function editForm() { return {}; }
</script>
@endsection

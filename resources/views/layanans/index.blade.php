@extends('layouts.app')
@section('title', 'Master layanan')

@section('content')
<div class="max-w-4xl space-y-5">

<div class="bg-white border border-gray-100 rounded-xl p-6">
    <h2 class="text-sm font-semibold text-gray-900 mb-4">Tambah layanan baru</h2>
    <form method="POST" action="{{ route('layanans.store') }}" class="flex flex-wrap gap-3 items-end">
        @csrf
        <div class="w-44">
            <label class="block text-xs text-gray-500 mb-1.5 font-medium">Kategori</label>
            <select name="kategori_layanan_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand bg-white" required>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs text-gray-500 mb-1.5">Nama layanan</label>
            <input type="text" name="nama" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
        </div>
        <div class="w-28">
            <label class="block text-xs text-gray-500 mb-1.5">Harga (Rp)</label>
            <input type="number" name="harga" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" min="1000" required>
        </div>
        <div class="w-16">
            <label class="block text-xs text-gray-500 mb-1.5">Estimasi</label>
            <input type="number" name="estimasi_nilai" value="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" min="1" required>
        </div>
        <div class="w-24">
            <label class="block text-xs text-gray-500 mb-1.5">Satuan</label>
            <select name="estimasi_satuan" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand bg-white" required>
                <option value="Hari">Hari</option>
                <option value="Jam">Jam</option>
            </select>
        </div>
        <button type="submit" class="bg-brand text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-brand-hover transition-colors whitespace-nowrap">Tambah</button>
    </form>
</div>

<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
        <span class="text-sm font-semibold text-gray-900">Daftar layanan</span>
        <a href="{{ route('hpp.index') }}" class="text-xs text-gray-400 hover:text-gray-700">Kelola Bahan Baku →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[700px]">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Kategori</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Layanan</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Harga</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">HPP</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Margin</th>
                    <th class="px-5 py-3 text-center text-xs font-medium text-gray-500">Estimasi</th>
                    <th class="px-5 py-3 text-center text-xs font-medium text-gray-500">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach($layanans as $l)
                <tr class="border-b border-gray-50 hover:bg-gray-50" x-data="{ edit: false }">
                    <td class="px-5 py-3 text-sm text-gray-500">
                        <span x-show="!edit">{{ $l->kategoriLayanan->nama ?? '-' }}</span>
                    </td>
                    <td class="px-5 py-3">
                        <span x-show="!edit" class="text-sm font-medium text-gray-900 {{ !$l->aktif ? 'line-through text-gray-400' : '' }}">
                            {{ $l->nama }}
                        </span>
                        <form x-show="edit" method="POST" action="{{ route('layanans.update', $l) }}"
                              class="flex flex-wrap gap-2 items-center">
                            @csrf @method('PUT')
                            <select name="kategori_layanan_id" class="rounded border border-gray-200 px-2 py-1 text-xs w-28 focus:outline-none focus:ring-1 focus:ring-brand bg-white" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $l->kategori_layanan_id == $cat->id ? 'selected' : '' }}>{{ $cat->nama }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="nama" value="{{ $l->nama }}"
                                   class="rounded border border-gray-200 px-2 py-1 text-xs w-32 focus:outline-none focus:ring-1 focus:ring-brand" required>
                            <input type="number" name="harga" value="{{ $l->harga }}"
                                   class="rounded border border-gray-200 px-2 py-1 text-xs w-20 focus:outline-none focus:ring-1 focus:ring-brand" min="1000" required>
                            <input type="number" name="estimasi_nilai" value="{{ $l->estimasi_nilai }}"
                                   class="rounded border border-gray-200 px-2 py-1 text-xs w-12 focus:outline-none focus:ring-1 focus:ring-brand" min="1" required>
                            <select name="estimasi_satuan" class="rounded border border-gray-200 px-2 py-1 text-xs w-16 focus:outline-none focus:ring-1 focus:ring-brand bg-white" required>
                                <option value="Hari" {{ $l->estimasi_satuan === 'Hari' ? 'selected' : '' }}>Hari</option>
                                <option value="Jam" {{ $l->estimasi_satuan === 'Jam' ? 'selected' : '' }}>Jam</option>
                            </select>
                            <button type="submit" class="text-xs text-green-700 font-medium hover:text-green-900">Simpan</button>
                            <button type="button" @click="edit = false" class="text-xs text-gray-400 hover:text-gray-700">Batal</button>
                        </form>
                    </td>
                    <td class="px-5 py-3 text-sm text-right text-gray-700">
                        <span x-show="!edit">{{ $l->harga_format }}</span>
                    </td>
                    <td class="px-5 py-3 text-sm text-right text-red-600">
                        @if($l->total_hpp > 0)
                        Rp {{ number_format($l->total_hpp, 0, ',', '.') }}
                        @else
                        <span class="text-gray-300 text-xs">Belum diset</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        @if($l->total_hpp > 0)
                        <span class="text-xs font-semibold {{ $l->gross_margin >= 70 ? 'text-green-700' : ($l->gross_margin >= 40 ? 'text-amber-700' : 'text-red-700') }}">
                            {{ $l->gross_margin }}%
                        </span>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs text-center text-gray-500">
                        <span x-show="!edit">{{ $l->estimasi_nilai }} {{ strtolower($l->estimasi_satuan) }}</span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                     {{ $l->aktif ? 'bg-green-50 text-green-700 ring-1 ring-green-200' : 'bg-gray-100 text-gray-500 ring-1 ring-gray-200' }}">
                            {{ $l->aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3" x-show="!edit">
                            <button @click="edit = true" class="text-xs text-gray-400 hover:text-gray-900">Edit</button>
                            <form method="POST" action="{{ route('layanans.toggle', $l) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="text-xs {{ $l->aktif ? 'text-amber-600 hover:text-amber-900' : 'text-green-600 hover:text-green-900' }} transition-colors">
                                    {{ $l->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

</div>
@endsection

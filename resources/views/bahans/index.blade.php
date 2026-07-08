@extends('layouts.app')
@section('title', 'Daftar bahan baku')

@section('content')
<div class="max-w-4xl space-y-5">

    <div class="bg-white border border-gray-100 rounded-xl p-6">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Tambah bahan baku baru</h2>
        <form method="POST" action="{{ route('bahans.store') }}" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs text-gray-500 mb-1.5 font-medium">Nama Bahan</label>
                <input type="text" name="nama" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required placeholder="e.g. Sabun Standard">
            </div>
            <div class="w-24">
                <label class="block text-xs text-gray-500 mb-1.5 font-medium">Satuan</label>
                <input type="text" name="satuan" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required placeholder="e.g. ml, pcs">
            </div>
            <div class="w-32">
                <label class="block text-xs text-gray-500 mb-1.5 font-medium">Harga Beli (Rp)</label>
                <input type="number" name="harga_beli" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required min="0" placeholder="e.g. 50000">
            </div>
            <div class="w-28">
                <label class="block text-xs text-gray-500 mb-1.5 font-medium">Isi Kemasan</label>
                <input type="number" name="isi_kemasan" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required min="1" placeholder="e.g. 1000">
            </div>
            <button type="submit" class="bg-brand text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-brand-hover transition-colors whitespace-nowrap">Tambah</button>
        </form>
    </div>

    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-900">Daftar Bahan Baku</span>
            <a href="{{ route('stok.index') }}" class="text-xs text-gray-400 hover:text-gray-700">Lihat Stok →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Nama Bahan</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-gray-500">Satuan</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Harga Beli</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-gray-500">Isi Kemasan</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Harga Satuan (Auto)</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-gray-500">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($bahans as $b)
                    <tr class="border-b border-gray-50 hover:bg-gray-50" x-data="{ edit: false }">
                        <td class="px-5 py-3">
                            <span x-show="!edit" class="text-sm font-medium text-gray-900 {{ !$b->aktif ? 'line-through text-gray-400' : '' }}">
                                {{ $b->nama }}
                            </span>
                            <form x-show="edit" method="POST" action="{{ route('bahans.update', $b) }}"
                                  class="flex flex-wrap gap-2 items-center">
                                @csrf @method('PUT')
                                <input type="text" name="nama" value="{{ $b->nama }}"
                                       class="rounded border border-gray-200 px-2 py-1 text-xs w-36 focus:outline-none focus:ring-1 focus:ring-brand" required>
                                <input type="text" name="satuan" value="{{ $b->satuan }}"
                                       class="rounded border border-gray-200 px-2 py-1 text-xs w-16 focus:outline-none focus:ring-1 focus:ring-brand" required>
                                <input type="number" name="harga_beli" value="{{ $b->harga_beli }}"
                                       class="rounded border border-gray-200 px-2 py-1 text-xs w-24 focus:outline-none focus:ring-1 focus:ring-brand" min="0" required>
                                <input type="number" name="isi_kemasan" value="{{ $b->isi_kemasan }}"
                                       class="rounded border border-gray-200 px-2 py-1 text-xs w-16 focus:outline-none focus:ring-1 focus:ring-brand" min="1" required>
                                <button type="submit" class="text-xs text-green-700 font-medium hover:text-green-900">Simpan</button>
                                <button type="button" @click="edit = false" class="text-xs text-gray-400 hover:text-gray-700">Batal</button>
                            </form>
                        </td>
                        <td class="px-5 py-3 text-sm text-center text-gray-700">{{ $b->satuan }}</td>
                        <td class="px-5 py-3 text-sm text-right text-gray-700">Rp {{ number_format($b->harga_beli, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-sm text-center text-gray-700">{{ number_format($b->isi_kemasan, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-sm text-right text-brand font-semibold">Rp {{ number_format($b->harga_satuan, 2, ',', '.') }} / {{ $b->satuan }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                         {{ $b->aktif ? 'bg-green-50 text-green-700 ring-1 ring-green-200' : 'bg-gray-100 text-gray-500 ring-1 ring-gray-200' }}">
                                {{ $b->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3" x-show="!edit">
                                <button @click="edit = true" class="text-xs text-gray-400 hover:text-gray-900">Edit</button>
                                <form method="POST" action="{{ route('bahans.toggle', $b) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="text-xs {{ $b->aktif ? 'text-amber-600 hover:text-amber-900' : 'text-green-600 hover:text-green-900' }} transition-colors">
                                        {{ $b->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
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

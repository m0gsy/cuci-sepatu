@extends('layouts.app')
@section('title', 'Jenis barang')

@section('content')
<div class="max-w-3xl space-y-5">

    <div class="bg-white border border-gray-100 rounded-xl p-6">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Tambah jenis barang baru</h2>
        <form method="POST" action="{{ route('jenis-barangs.store') }}" class="flex gap-3 items-end">
            @csrf
            <div class="flex-1">
                <label class="block text-xs text-gray-500 mb-1.5">Nama jenis barang</label>
                <input type="text" name="nama" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
            </div>
            <button type="submit" class="bg-brand text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-brand-hover transition-colors whitespace-nowrap">Tambah</button>
        </form>
    </div>

    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <span class="text-sm font-semibold text-gray-900">Daftar jenis barang</span>
        </div>
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Jenis Barang</th>
                    <th class="px-5 py-3 text-center text-xs font-medium text-gray-500">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach($itemTypes as $type)
                <tr class="border-b border-gray-50 hover:bg-gray-50" x-data="{ edit: false }">
                    <td class="px-5 py-3">
                        <span x-show="!edit" class="text-sm font-medium text-gray-900 {{ !$type->aktif ? 'line-through text-gray-400' : '' }}">
                            {{ $type->nama }}
                        </span>
                        <form x-show="edit" method="POST" action="{{ route('jenis-barangs.update', $type) }}"
                              class="flex gap-2 items-center">
                            @csrf @method('PUT')
                            <input type="text" name="nama" value="{{ $type->nama }}"
                                   class="rounded border border-gray-200 px-2 py-1 text-sm w-48 focus:outline-none focus:ring-1 focus:ring-brand" required>
                            <button type="submit" class="text-xs text-green-700 font-medium hover:text-green-900">Simpan</button>
                            <button type="button" @click="edit = false" class="text-xs text-gray-400 hover:text-gray-700">Batal</button>
                        </form>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                     {{ $type->aktif ? 'bg-green-50 text-green-700 ring-1 ring-green-200' : 'bg-gray-100 text-gray-500 ring-1 ring-gray-200' }}">
                            {{ $type->aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3" x-show="!edit">
                            <button @click="edit = true" class="text-xs text-gray-400 hover:text-gray-900">Edit</button>
                            <form method="POST" action="{{ route('jenis-barangs.toggle', $type) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="text-xs {{ $type->aktif ? 'text-amber-600 hover:text-amber-900' : 'text-green-600 hover:text-green-900' }} transition-colors">
                                    {{ $type->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
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
@endsection

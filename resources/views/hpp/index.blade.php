@extends('layouts.app')
@section('title', 'Kelola Resep HPP Layanan')

@section('header-actions')
<a href="{{ route('hpp.laporan') }}"
   class="text-xs border border-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
    Laporan Profit/Loss →
</a>
@endsection

@section('content')
<div class="max-w-5xl space-y-5">

<div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-4">
    <p class="text-sm font-medium text-blue-800 mb-1">Kelola Bahan Baku Layanan (HPP Resep)</p>
    <p class="text-xs text-blue-700">
        Tentukan resep penggunaan bahan baku untuk setiap layanan. HPP layanan otomatis dihitung dari akumulasi biaya bahan baku yang digunakan.
        Formulasinya: <strong class="text-blue-900">Total HPP = Jumlah Penggunaan × Harga Satuan Bahan Baku</strong>.
    </p>
</div>

<div class="space-y-6">
@foreach($layanans as $l)
<div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm">

    {{-- Header layanan --}}
    <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap gap-4 items-center justify-between bg-gray-50">
        <div>
            <span class="inline-block bg-brand/10 text-brand text-xs font-semibold px-2 py-0.5 rounded mb-1">
                {{ $l->kategoriLayanan->nama ?? 'Umum' }}
            </span>
            <p class="text-sm font-semibold text-gray-900">{{ $l->nama }}</p>
            <p class="text-xs text-gray-500">Harga jual: {{ $l->harga_format }} / pasang</p>
        </div>
        <div class="text-right">
            <div class="flex items-center gap-4">
                <div>
                    <p class="text-xs text-gray-400">Total HPP / pasang</p>
                    <p class="text-lg font-bold text-red-600">
                        Rp {{ number_format($l->total_hpp, 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Gross Profit / pasang</p>
                    <p class="text-lg font-bold text-green-700">
                        Rp {{ number_format($l->harga - $l->total_hpp, 0, ',', '.') }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-400">Margin</p>
                    @php $margin = $l->gross_margin; @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                 {{ $margin >= 70 ? 'bg-green-100 text-green-800' :
                                   ($margin >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                        {{ $margin }}%
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel resep bahan --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-[700px]">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500">Bahan Baku</th>
                    <th class="px-5 py-2.5 text-center text-xs font-medium text-gray-500">Jumlah Penggunaan</th>
                    <th class="px-5 py-2.5 text-right text-xs font-medium text-gray-500">Harga Satuan</th>
                    <th class="px-5 py-2.5 text-right text-xs font-medium text-gray-500">Total Biaya</th>
                    <th class="px-5 py-2.5 text-right text-xs font-medium text-gray-500">% HPP</th>
                    <th class="px-5 py-2.5 text-center text-xs font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($l->recipes as $r)
            <tr class="border-b border-gray-50 hover:bg-gray-50" x-data="{ edit: false }">
                <td class="px-5 py-3">
                    <span x-show="!edit" class="text-sm font-medium text-gray-800">{{ $r->bahan->nama }}</span>
                    <span x-show="!edit" class="text-xs text-gray-400 block">Bahan Baku ID: #{{ $r->bahan_id }}</span>
                    <form x-show="edit" method="POST" action="{{ route('hpp.update', $r->id) }}"
                          class="flex gap-2 items-center">
                        @csrf @method('PUT')
                        <input type="number" name="jumlah_penggunaan" value="{{ $r->jumlah_penggunaan }}" step="0.01"
                               class="rounded border border-gray-200 px-2 py-1 text-sm w-28 focus:outline-none focus:ring-1 focus:ring-brand" min="0.01" required>
                        <button type="submit" class="text-xs text-green-700 font-medium hover:text-green-900">Simpan</button>
                        <button type="button" @click="edit = false" class="text-xs text-gray-400 hover:text-gray-700">Batal</button>
                    </form>
                </td>
                <td class="px-5 py-3 text-center text-sm text-gray-700" x-show="!edit">
                    {{ number_format($r->jumlah_penggunaan, 2, ',', '.') }} {{ $r->bahan->satuan }}
                </td>
                <td class="px-5 py-3 text-right text-sm text-gray-500" x-show="!edit">
                    Rp {{ number_format($r->bahan->harga_satuan, 2, ',', '.') }} / {{ $r->bahan->satuan }}
                </td>
                <td class="px-5 py-3 text-right text-sm font-semibold text-gray-900" x-show="!edit">
                    Rp {{ number_format($r->total_cost, 0, ',', '.') }}
                </td>
                <td class="px-5 py-3 text-right text-xs text-gray-400" x-show="!edit">
                    {{ $l->total_hpp > 0 ? round(($r->total_cost / $l->total_hpp) * 100, 1) : 0 }}%
                </td>
                <td class="px-5 py-3 text-center" x-show="!edit">
                    <div class="flex items-center justify-center gap-3">
                        <button @click="edit = true"
                                class="text-xs text-gray-400 hover:text-gray-900">Edit</button>
                        <form method="POST" action="{{ route('hpp.destroy', $r->id) }}"
                              onsubmit="return confirm('Hapus komponen resep ini?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-4 text-center text-xs text-gray-400 bg-gray-50/20">
                    Belum ada bahan baku resep yang diset untuk layanan ini.
                </td>
            </tr>
            @endforelse

            {{-- Form tambah komponen baru --}}
            <tr class="bg-gray-50 border-t border-gray-100">
                <td colspan="6" class="px-5 py-3">
                    <form method="POST" action="{{ route('hpp.store') }}" class="flex flex-wrap gap-3 items-center">
                        @csrf
                        <input type="hidden" name="layanan_id" value="{{ $l->id }}">
                        <div class="flex-1 min-w-[220px]">
                            <select name="bahan_id" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand bg-white" required>
                                <option value="">-- Pilih Bahan Baku --</option>
                                @foreach($bahans as $b)
                                    <option value="{{ $b->id }}">{{ $b->nama }} (Rp {{ number_format($b->harga_satuan, 2, ',', '.') }} / {{ $b->satuan }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-36">
                            <input type="number" name="jumlah_penggunaan" min="0.01" step="0.01"
                                   class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand"
                                   placeholder="Jumlah Penggunaan" required>
                        </div>
                        <button type="submit"
                                class="bg-brand text-white text-xs font-medium px-4 py-2 rounded-lg hover:bg-brand-hover whitespace-nowrap">
                            + Tambah Resep
                        </button>
                    </form>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

</div>
@endforeach
</div>

</div>
@endsection

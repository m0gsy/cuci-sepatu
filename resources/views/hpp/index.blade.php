@extends('layouts.app')
@section('title', 'Kelola HPP Layanan')

@section('header-actions')
<a href="{{ route('hpp.laporan') }}"
   class="text-xs border border-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
    Laporan Profit/Loss →
</a>
@endsection

@section('content')
<div class="max-w-4xl space-y-5">

<div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-4">
    <p class="text-sm font-medium text-blue-800 mb-1">HPP (Harga Pokok Penjualan)</p>
    <p class="text-xs text-blue-700">
        Isi komponen biaya untuk setiap layanan. HPP akan otomatis dihitung saat order baru dibuat.
        Gross Profit = Net Sales − HPP. Makin kecil HPP, makin besar margin.
    </p>
</div>

<div class="space-y-4">
@foreach($layanans as $l)
<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">

    {{-- Header layanan --}}
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
        <div>
            <p class="text-sm font-semibold text-gray-900">{{ $l->nama }}</p>
            <p class="text-xs text-gray-500">Harga jual: {{ $l->harga_format }} / pasang</p>
        </div>
        <div class="text-right">
            <div class="flex items-center gap-4">
                <div>
                    <p class="text-xs text-gray-400">Total HPP / pasang</p>
                    <p class="text-lg font-semibold text-red-700">
                        Rp {{ number_format($l->total_hpp, 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Gross Profit / pasang</p>
                    <p class="text-lg font-semibold text-green-700">
                        Rp {{ number_format($l->harga - $l->total_hpp, 0, ',', '.') }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-400">Margin</p>
                    @php $margin = $l->gross_margin; @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold
                                 {{ $margin >= 70 ? 'bg-green-100 text-green-800' :
                                   ($margin >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                        {{ $margin }}%
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel komponen HPP --}}
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100">
                <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500">Komponen biaya</th>
                <th class="px-5 py-2.5 text-right text-xs font-medium text-gray-500">Biaya / pasang (Rp)</th>
                <th class="px-5 py-2.5 text-right text-xs font-medium text-gray-500">% dari HPP</th>
                <th class="px-5 py-2.5 text-center text-xs font-medium text-gray-500">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($l->hppKomponen as $h)
        <tr class="border-b border-gray-50 hover:bg-gray-50" x-data="{ edit: false }">
            <td class="px-5 py-3">
                <span x-show="!edit" class="text-sm text-gray-800">{{ $h->komponen }}</span>
                <form x-show="edit" method="POST" action="{{ route('hpp.update', $h) }}"
                      class="flex gap-2 items-center">
                    @csrf @method('PUT')
                    <input type="text" name="komponen" value="{{ $h->komponen }}"
                           class="rounded-lg border border-gray-200 px-2 py-1 text-sm w-40 focus:outline-none focus:ring-1 focus:ring-brand">
                    <input type="number" name="biaya" value="{{ $h->biaya }}"
                           class="rounded-lg border border-gray-200 px-2 py-1 text-sm w-28 focus:outline-none focus:ring-1 focus:ring-brand" min="0">
                    <button type="submit" class="text-xs text-green-700 font-medium hover:text-green-900">Simpan</button>
                    <button type="button" @click="edit = false" class="text-xs text-gray-400 hover:text-gray-700">Batal</button>
                </form>
            </td>
            <td class="px-5 py-3 text-right text-sm font-medium text-gray-900" x-show="!edit">
                Rp {{ number_format($h->biaya, 0, ',', '.') }}
            </td>
            <td class="px-5 py-3 text-right text-xs text-gray-500" x-show="!edit">
                {{ $l->total_hpp > 0 ? round(($h->biaya / $l->total_hpp) * 100, 1) : 0 }}%
            </td>
            <td class="px-5 py-3 text-center" x-show="!edit">
                <div class="flex items-center justify-center gap-2">
                    <button @click="edit = true"
                            class="text-xs text-gray-400 hover:text-gray-700">Edit</button>
                    <form method="POST" action="{{ route('hpp.destroy', $h) }}"
                          onsubmit="return confirm('Hapus komponen ini?')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="px-5 py-4 text-center text-xs text-gray-400">
                Belum ada komponen HPP. Tambahkan di bawah.
            </td>
        </tr>
        @endforelse

        {{-- Form tambah komponen baru --}}
        <tr class="bg-gray-50 border-t border-gray-100">
            <td colspan="4" class="px-5 py-3">
                <form method="POST" action="{{ route('hpp.store') }}" class="flex gap-3 items-center">
                    @csrf
                    <input type="hidden" name="layanan_id" value="{{ $l->id }}">
                    <input type="text" name="komponen"
                           class="flex-1 rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                           placeholder="Nama komponen (misal: Sabun, Listrik, Tenaga)" required>
                    <input type="number" name="biaya" min="0"
                           class="w-36 rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                           placeholder="Biaya (Rp)" required>
                    <button type="submit"
                            class="bg-brand text-white text-xs font-medium px-4 py-1.5 rounded-lg hover:bg-brand-hover whitespace-nowrap">
                        + Tambah
                    </button>
                </form>
            </td>
        </tr>
        </tbody>
    </table>

</div>
@endforeach
</div>

</div>
@endsection

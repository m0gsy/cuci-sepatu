@extends('layouts.app')
@section('title', 'Riwayat — ' . $pelanggan->nama)

@section('header-actions')
    <a href="{{ $pelanggan->wa_link }}" target="_blank"
       class="text-xs border border-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-50">Chat WA ↗</a>
    <a href="{{ route('pelanggans.index') }}" class="text-xs text-gray-400 hover:text-gray-700">← Kembali</a>
@endsection

@section('content')
<div class="max-w-3xl space-y-5">

    {{-- Profil --}}
    <div class="bg-white border border-gray-100 rounded-xl p-6" x-data="{ edit: false }">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-lg font-semibold text-gray-600">
                    {{ strtoupper(substr($pelanggan->nama, 0, 1)) }}
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-900">{{ $pelanggan->nama }}</p>
                    <p class="text-sm text-gray-500">{{ $pelanggan->no_hp }}</p>
                    @if($pelanggan->alamat)<p class="text-xs text-gray-400">{{ $pelanggan->alamat }}</p>@endif
                </div>
            </div>
            <button @click="edit = !edit"
                    class="text-xs text-gray-400 hover:text-gray-700 border border-gray-200 px-3 py-1.5 rounded-lg">
                Edit
            </button>
        </div>
        <div x-show="edit" class="border-t border-gray-100 pt-4">
            <form method="POST" action="{{ route('pelanggans.update', $pelanggan) }}">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nama</label>
                        <input type="text" name="nama" value="{{ $pelanggan->nama }}"
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">No. HP</label>
                        <input type="text" name="no_hp" value="{{ $pelanggan->no_hp }}"
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Alamat</label>
                        <input type="text" name="alamat" value="{{ $pelanggan->alamat }}"
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 text-sm bg-brand text-white rounded-lg hover:bg-brand-hover">Simpan</button>
                    <button type="button" @click="edit = false" class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg">Batal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-4 gap-3">
        @php
            $statCards = [
                ['label' => 'Total order',     'value' => $stats['total_order'] . ' order'],
                ['label' => 'Total belanja',   'value' => 'Rp ' . number_format($stats['total_belanja'], 0, ',', '.')],
                ['label' => 'Total pasang',    'value' => $stats['total_pasang'] . ' pasang'],
                ['label' => 'Layanan favorit', 'value' => $stats['layanan_favorit'] ?? '—'],
            ];
        @endphp
        @foreach($statCards as $sc)
        <div class="bg-gray-50 rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1.5">{{ $sc['label'] }}</p>
            <p class="text-sm font-semibold text-gray-900 leading-tight">{{ $sc['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Rekap per layanan --}}
    @if($rekapLayanan->count() > 0)
    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <span class="text-sm font-semibold text-gray-900">Rekap per layanan</span>
        </div>
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Layanan</th>
                    <th class="px-5 py-3 text-center text-xs font-medium text-gray-500">Frekuensi</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Total pasang</th>
                </tr>
            </thead>
            <tbody>
            @foreach($rekapLayanan as $r)
                <tr class="border-b border-gray-50">
                    <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $r->layanan->nama }}</td>
                    <td class="px-5 py-3 text-sm text-center text-gray-700">{{ $r->jumlah }}×</td>
                    <td class="px-5 py-3 text-sm text-right text-gray-700">{{ $r->total_pasang }} pasang</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Semua order --}}
    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-900">Semua order</span>
            <span class="text-xs text-gray-400">{{ $stats['total_order'] }} total</span>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full min-w-[520px]">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 whitespace-nowrap">No. order</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 whitespace-nowrap">Tanggal</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Layanan</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Lokasi</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 whitespace-nowrap">Total</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $o)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 whitespace-nowrap">
                        <a href="{{ route('orders.show', $o) }}"
                           class="text-xs font-mono text-gray-500 hover:text-gray-900">{{ $o->no_order }}</a>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $o->created_at->isoFormat('D MMM Y') }}</td>
                    <td class="px-5 py-3 text-sm text-gray-700">{{ $o->layanan->nama }}</td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        @if($o->lokasi)
                        <span class="text-xs font-mono font-bold bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded">
                            {{ $o->lokasi->kode }}
                        </span>
                        @else
                        <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-sm font-medium text-right text-gray-900 whitespace-nowrap">
                        {{ $o->pembayaran?->total_format ?? '—' }}
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $o->status_badge['class'] }}">
                            {{ $o->status_badge['label'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-8 text-center text-sm text-gray-400">Belum ada order.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
        @if($orders->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $orders->links() }}</div>
        @endif
    </div>

</div>
@endsection

@extends('layouts.app')
@section('title', 'Pekerjaan saya')

@section('content')
<div class="space-y-5">
@php
    $byStatus = $aktif->groupBy('status');
    $urutan = [
        'draft' => 'Draft',
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'diproses' => 'Diproses',
        'siap_diambil' => 'Siap Diambil',
    ];
@endphp

{{-- Summary cards --}}
<div class="grid grid-cols-3 gap-4">
    <div class="bg-white border border-gray-100 rounded-xl p-4">
        <p class="text-xs text-gray-400 mb-1">Total aktif</p>
        <p class="text-2xl font-semibold text-gray-900">{{ $aktif->count() }}</p>
        <p class="text-xs text-gray-400 mt-1">order dalam proses</p>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4">
        <p class="text-xs text-gray-400 mb-1">Hari ini masuk</p>
        <p class="text-2xl font-semibold text-gray-900">{{ $aktif->filter(fn($o) => $o->created_at->isToday())->count() }}</p>
        <p class="text-xs text-gray-400 mt-1">order baru</p>
    </div>
    <div class="bg-white border {{ $terlambat > 0 ? 'border-red-200 bg-red-50' : 'border-gray-100' }} rounded-xl p-4">
        <p class="text-xs {{ $terlambat > 0 ? 'text-red-500' : 'text-gray-400' }} mb-1">Terlambat</p>
        <p class="text-2xl font-semibold {{ $terlambat > 0 ? 'text-red-700' : 'text-gray-900' }}">{{ $terlambat }}</p>
        <p class="text-xs {{ $terlambat > 0 ? 'text-red-400' : 'text-gray-400' }} mt-1">lewat estimasi</p>
    </div>
</div>

{{-- Pekerjaan per status --}}
@foreach($urutan as $statusKey => $statusLabel)
@php $items = $byStatus[$statusKey] ?? collect(); @endphp
@if($items->isNotEmpty())
<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
        <span class="text-sm font-semibold text-gray-900">{{ $statusLabel }}</span>
        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">{{ $items->count() }}</span>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full min-w-[500px]">
        <thead>
            <tr class="border-b border-gray-50 bg-gray-50">
                <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500 whitespace-nowrap">No. order</th>
                <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500">Pelanggan</th>
                <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500">Layanan</th>
                <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500 whitespace-nowrap">Est. selesai</th>
                <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500">Lokasi</th>
                <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500"></th>
            </tr>
        </thead>
        <tbody>
        @foreach($items as $o)
        @php $late = $o->estimasi_selesai->isPast(); @endphp
        <tr class="border-b border-gray-50 hover:bg-gray-50 {{ $late ? 'bg-red-50/40' : '' }}">
            <td class="px-5 py-3 whitespace-nowrap">
                <a href="{{ route('orders.show', $o) }}" class="text-xs font-mono text-gray-500 hover:text-gray-900">{{ $o->no_order }}</a>
            </td>
            <td class="px-5 py-3 text-sm text-gray-800">{{ $o->nama_pelanggan }}</td>
            <td class="px-5 py-3 text-xs text-gray-500">
                {{ $o->items->isNotEmpty()
                    ? $o->items->map(fn($i) => $i->layanan->nama ?? '—')->join(', ')
                    : ($o->layanan->nama ?? '—') }}
            </td>
            <td class="px-5 py-3 text-xs whitespace-nowrap {{ $late ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                {{ $o->estimasi_selesai->isoFormat('D MMM') }}
                @if($late) <span class="text-red-400">(terlambat)</span> @endif
            </td>
            <td class="px-5 py-3 text-xs text-gray-400">{{ $o->lokasi?->nama ?? '—' }}</td>
            <td class="px-5 py-3 whitespace-nowrap">
                <a href="{{ route('orders.show', $o) }}" class="text-xs text-brand hover:underline">Buka →</a>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif
@endforeach

@if($aktif->isEmpty())
<div class="bg-white border border-gray-100 rounded-xl p-12 text-center">
    <p class="text-sm text-gray-400">Tidak ada pekerjaan aktif saat ini.</p>
</div>
@endif
</div>
@endsection

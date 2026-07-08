@extends('layouts.app')
@section('title', 'Laporan penjualan')

@section('header-actions')
    <a href="{{ route('laporan.excel', ['bulan' => $bulan]) }}"
       class="text-xs border border-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
        Ekspor Excel ↗
    </a>
    <a href="{{ route('laporan.pdf', ['bulan' => $bulan]) }}"
       class="text-xs border border-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
        Ekspor PDF ↗
    </a>
@endsection

@section('content')
<div class="space-y-5">

<form method="GET" class="flex items-center gap-3">
    <label class="text-xs text-gray-500">Pilih bulan:</label>
    <input type="month" name="bulan" value="{{ $bulan }}"
           class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
           onchange="this.form.submit()">
</form>

@php
    $totalOrder = $rekap->sum('orders_count');
    $rata2 = $totalOrder > 0 ? intdiv($total_bulan, $totalOrder) : 0;
@endphp

<div class="grid grid-cols-3 gap-3 mb-6">
    <div class="bg-white border border-gray-100 rounded-xl p-4">
        <p class="text-xs text-gray-400 mb-2">Total pendapatan</p>
        <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($total_bulan, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-1">sudah lunas</p>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4">
        <p class="text-xs text-gray-400 mb-2">Total order</p>
        <p class="text-2xl font-semibold text-gray-900">{{ $totalOrder }}</p>
        <p class="text-xs text-gray-400 mt-1">bulan ini</p>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4">
        <p class="text-xs text-gray-400 mb-2">Rata-rata per order</p>
        <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($rata2, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-1">per transaksi</p>
    </div>
</div>

<div class="bg-white border border-gray-100 rounded-xl overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <span class="text-sm font-semibold text-gray-900">Rekap per layanan</span>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Layanan</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Harga satuan</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Jumlah pasang</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Total pendapatan</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rekap as $l)
            <tr class="border-b border-gray-50 hover:bg-gray-50">
                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $l->nama }}</td>
                <td class="px-5 py-3 text-sm text-right text-gray-500">Rp {{ number_format($l->harga, 0, ',', '.') }}</td>
                <td class="px-5 py-3 text-sm text-right text-gray-900">{{ $l->total_jumlah_pasang ?? 0 }} pasang</td>
                <td class="px-5 py-3 text-sm text-right font-semibold text-gray-900">
                    Rp {{ number_format($l->total_pendapatan ?? 0, 0, ',', '.') }}
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-400">Belum ada data bulan ini.</td></tr>
        @endforelse
        </tbody>
        @if($rekap->isNotEmpty())
        <tfoot>
            <tr class="border-t border-gray-200 bg-gray-50">
                <td class="px-5 py-3 text-sm font-semibold text-gray-900" colspan="2">Total</td>
                <td class="px-5 py-3 text-sm text-right font-semibold text-gray-900">{{ $rekap->sum('total_jumlah_pasang') }} pasang</td>
                <td class="px-5 py-3 text-sm text-right font-semibold text-gray-900">Rp {{ number_format($total_bulan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <span class="text-sm font-semibold text-gray-900">Detail order</span>
        <span class="text-xs text-gray-400 ml-2">{{ $orders->count() }} order</span>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">No. order</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Pelanggan</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Layanan</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">Total</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse($orders as $o)
            <tr class="border-b border-gray-50 hover:bg-gray-50">
                <td class="px-4 py-3 whitespace-nowrap">
                    <a href="{{ route('orders.show', $o) }}"
                       class="text-xs font-mono text-gray-500 hover:text-gray-900">{{ $o->no_order }}</a>
                </td>
                <td class="px-4 py-3 text-sm text-gray-800">{{ $o->nama_pelanggan }}</td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    {{ $o->items->isNotEmpty()
                        ? $o->items->map(fn($i) => $i->layanan->nama ?? '—')->join(', ')
                        : ($o->layanan->nama ?? '—') }}
                </td>
                <td class="px-4 py-3 text-sm font-medium text-right text-gray-900 whitespace-nowrap">{{ $o->pembayaran?->total_format ?? '—' }}</td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $o->status_badge['class'] }}">
                        {{ $o->status_badge['label'] }}
                    </span>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">Tidak ada order bulan ini.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if($orders->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">
        {{ $orders->links() }}
    </div>
    @endif
</div>

</div>
@endsection

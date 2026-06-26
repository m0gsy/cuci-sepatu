@extends('layouts.app')
@section('title', 'Detail — ' . $karyawan->name)

@section('header-actions')
    <a href="{{ route('karyawans.index') }}" class="text-xs text-gray-400 hover:text-gray-700">← Kembali</a>
@endsection

@section('content')
<div class="max-w-3xl space-y-5">

    <div class="bg-white border border-gray-100 rounded-xl p-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center text-lg font-semibold text-gray-600 shrink-0">
                {{ $karyawan->inisial }}
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <p class="text-lg font-semibold text-gray-900">{{ $karyawan->name }}</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $karyawan->role_badge }}">
                        {{ $karyawan->role_label }}
                    </span>
                </div>
                <p class="text-sm text-gray-500">{{ $karyawan->email }}</p>
                @if($karyawan->last_login)
                <p class="text-xs text-gray-400 mt-1">Login terakhir: {{ $karyawan->last_login->isoFormat('D MMM Y, HH:mm') }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-4 gap-3">
        @php
            $statCards = [
                ['label' => 'Total order',    'value' => number_format($stats['total_order']) . ' order'],
                ['label' => 'Bulan ini',      'value' => number_format($stats['order_bulan_ini']) . ' order'],
                ['label' => 'Hari ini',       'value' => number_format($stats['order_hari_ini']) . ' order'],
                ['label' => 'Total diproses', 'value' => 'Rp ' . number_format($stats['total_diproses'], 0, ',', '.')],
            ];
        @endphp
        @foreach($statCards as $sc)
        <div class="bg-gray-50 rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1.5">{{ $sc['label'] }}</p>
            <p class="text-sm font-semibold text-gray-900 leading-tight">{{ $sc['value'] }}</p>
        </div>
        @endforeach
    </div>

    @if($rekapBulanan->count() > 0)
    @php $months_id = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']; $maxOrder = $rekapBulanan->max('jumlah'); @endphp
    <div class="bg-white border border-gray-100 rounded-xl p-5">
        <p class="text-sm font-semibold text-gray-900 mb-4">Order 6 bulan terakhir</p>
        <div class="flex items-end gap-3 h-28">
            @foreach($rekapBulanan as $rb)
            @php $h = $maxOrder > 0 ? max(6, ($rb->jumlah / $maxOrder) * 88) : 6; @endphp
            <div class="flex flex-col items-center gap-1 flex-1">
                <span class="text-xs text-gray-500">{{ $rb->jumlah }}</span>
                <div class="w-full bg-brand rounded-t" style="height: {{ $h }}px"></div>
                <span class="text-xs text-gray-400">{{ $months_id[$rb->bulan] }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-900">Order yang diproses</span>
            <span class="text-xs text-gray-400">{{ $stats['total_order'] }} total</span>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full min-w-[520px]">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 whitespace-nowrap">No. order</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Pelanggan</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Layanan</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 whitespace-nowrap">Total</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 whitespace-nowrap">Tanggal</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $o)
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="px-5 py-3 whitespace-nowrap"><a href="{{ route('orders.show', $o) }}" class="text-xs font-mono text-gray-500 hover:text-gray-900">{{ $o->no_order }}</a></td>
                    <td class="px-5 py-3 text-sm text-gray-800">{{ $o->nama_pelanggan }}</td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $o->layanan->nama }}</td>
                    <td class="px-5 py-3 text-sm font-medium text-right text-gray-900 whitespace-nowrap">{{ $o->pembayaran?->total_format ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $o->created_at->isoFormat('D MMM Y') }}</td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $o->status_badge['class'] }}">{{ $o->status_badge['label'] }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">Belum ada order.</td></tr>
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

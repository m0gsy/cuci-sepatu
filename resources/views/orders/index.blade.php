@extends('layouts.app')
@section('title', 'Daftar order')

@section('header-actions')
    @if(auth()->user()->hasPermission('orders.manage'))
    <a href="{{ route('orders.create') }}"
       class="bg-brand text-white text-xs font-medium px-4 py-2 rounded-lg hover:bg-brand-hover transition-colors">
        + Order baru
    </a>
    @endif
@endsection

@section('content')
<div class="space-y-5">

<form method="GET" class="flex gap-3">
    <select name="status" onchange="this.form.submit()"
            class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
        <option value="">Semua status</option>
        @foreach([
            'draft'               => 'Draft',
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'diproses'            => 'Diproses',
            'siap_diambil'        => 'Siap Diambil',
            'selesai'             => 'Selesai',
            'batal'               => 'Batal',
        ] as $val => $label)
            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <div class="flex-1 flex gap-2">
        <input type="text" name="cari" value="{{ request('cari') }}"
               placeholder="Cari nama / no. order / no. HP..."
               class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
        <button type="submit" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Cari</button>
    </div>
    @if(request('cari') || request('status'))
    <a href="{{ route('orders.index') }}"
       class="px-4 py-2 text-sm text-gray-400 hover:text-gray-700 border border-gray-200 rounded-lg">Reset</a>
    @endif
</form>

<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full min-w-[860px]">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 whitespace-nowrap">No. order</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Pelanggan</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Layanan</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Lokasi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 whitespace-nowrap">Estimasi</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 whitespace-nowrap">Total</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 whitespace-nowrap">Profit</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Bayar</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($orders as $o)
            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 whitespace-nowrap">
                     <a href="{{ route('orders.show', $o) }}"
                        class="text-xs font-mono text-gray-500 hover:text-gray-900">{{ $o->no_order }}</a>
                </td>
                <td class="px-4 py-3">
                    @if($o->pelanggan && auth()->user()->hasPermission('pelanggan'))
                    <a href="{{ route('pelanggans.show', $o->pelanggan) }}"
                       class="text-sm font-medium text-gray-900 hover:underline">{{ $o->nama_pelanggan }}</a>
                    @else
                    <p class="text-sm font-medium text-gray-900">{{ $o->nama_pelanggan }}</p>
                    @endif
                    <p class="text-xs text-gray-400">{{ $o->no_hp_display }}</p>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    @if($o->items->count() > 1)
                        {{ $o->items->first()->layanan->nama ?? '—' }} (+{{ $o->items->count() - 1 }} lainnya)
                    @else
                        {{ $o->items->first()->layanan->nama ?? '—' }}
                    @endif
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    @if($o->lokasi)
                    <span class="text-xs font-mono font-bold bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded">
                        {{ $o->lokasi->kode }}
                    </span>
                    @else
                    <span class="text-xs text-gray-300">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="text-xs text-gray-500">{{ $o->estimasi_selesai?->format('d M Y') ?? '—' }}</span>
                    @if($o->terlambat)
                    <span class="block text-xs text-red-500 font-medium">Terlambat</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm font-medium text-right text-gray-900 whitespace-nowrap">
                    Rp {{ number_format($o->pembayaran?->total ?? 0, 0, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    @if($o->hpp > 0)
                    <span class="text-xs font-medium text-green-700">
                        Rp {{ number_format($o->gross_profit, 0, ',', '.') }}
                    </span>
                    <span class="block text-xs text-gray-400">{{ $o->gross_margin }}%</span>
                    @else
                    <span class="text-xs text-gray-300">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    @php $s = $o->pembayaran?->status; @endphp
                    <span class="text-xs font-medium px-2 py-0.5 rounded {{ $s === 'selesai' ? 'text-green-700 bg-green-50' : 'text-amber-700 bg-amber-50' }}">
                        {{ $s === 'selesai' ? 'Selesai' : 'Belum Selesai' }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $o->status_badge['class'] }}">
                        {{ $o->status_badge['label'] }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <a href="{{ route('orders.show', $o) }}"
                       class="text-xs text-gray-400 hover:text-gray-900 transition-colors">Detail →</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="px-4 py-12 text-center text-sm text-gray-400">
                    @if(request('cari') || request('status'))
                        Tidak ada order yang cocok.
                    @else
                        Belum ada order.
                        @if(auth()->user()->hasPermission('orders.manage'))<a href="{{ route('orders.create') }}" class="text-gray-700 underline ml-1">Buat order pertama</a>@endif
                    @endif
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>
    @if($orders->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">{{ $orders->links() }}</div>
    @endif
</div>

</div>
@endsection

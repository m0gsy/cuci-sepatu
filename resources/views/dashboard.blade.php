@extends('layouts.app')
@section('title', 'Dashboard')

@section('header-actions')
    @if(!auth()->user()->isCleaner())
    <a href="{{ route('orders.create') }}"
       class="bg-brand text-white text-xs font-medium px-4 py-2 rounded-lg hover:bg-brand-hover transition-colors">
        + Order baru
    </a>
    @endif
@endsection

@section('content')
<div class="space-y-5">

@if($terlambat > 0)
<div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3.5 flex items-center gap-3">
    <span class="text-amber-700 font-medium text-sm">⚠ {{ $terlambat }} order melewati estimasi selesai</span>
    <a href="{{ route('orders.index', ['status' => 'antri']) }}"
       class="text-xs text-amber-600 underline hover:text-amber-900 ml-auto">Lihat order</a>
</div>
@endif

@if($stokMenipis->count() > 0 && auth()->user()->hasPermission('stok'))
<div class="bg-red-50 border border-red-200 rounded-xl px-5 py-3.5 flex items-center gap-3">
    <div class="flex-1">
        <span class="text-red-700 font-medium text-sm">
            ⚠ {{ $stokMenipis->count() }} bahan {{ $stokMenipis->contains(fn($s) => $s->status_stok === 'habis') ? 'habis / ' : '' }}perlu restok
        </span>
        <span class="text-xs text-red-600 ml-2">{{ $stokMenipis->pluck('nama')->join(', ') }}</span>
    </div>
    <a href="{{ route('stok.index') }}"
       class="text-xs text-red-600 underline hover:text-red-900 shrink-0">Kelola stok</a>
</div>
@endif

{{-- ── SALES SUMMARY BULAN INI ─────────────────────────────────────── --}}
<div>
    <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
            Sales Summary — {{ now()->isoFormat('MMMM Y') }}
        </p>
        @if(auth()->user()->isOwner())
        <a href="{{ route('hpp.laporan') }}"
           class="text-xs text-gray-400 hover:text-gray-700">Laporan lengkap →</a>
        @endif
    </div>
    <div class="grid grid-cols-3 gap-3 mb-3">
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Gross Sales</p>
            <p class="text-xl font-semibold text-gray-900">
                Rp {{ number_format($statsBulan['gross_sales'], 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Net Sales</p>
            <p class="text-xl font-semibold text-gray-900">
                Rp {{ number_format($statsBulan['net_sales'], 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Gross Profit</p>
            <p class="text-xl font-semibold text-green-700">
                Rp {{ number_format($statsBulan['gross_profit'], 0, ',', '.') }}
            </p>
        </div>
    </div>
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Transactions</p>
            <p class="text-xl font-semibold text-gray-900">{{ $statsBulan['transactions'] }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Avg per Transaksi</p>
            <p class="text-xl font-semibold text-gray-900">
                Rp {{ number_format($statsBulan['avg_per_transaksi'], 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Gross Margin</p>
            <p class="text-xl font-semibold {{ $statsBulan['gross_margin'] >= 70 ? 'text-green-700' : ($statsBulan['gross_margin'] >= 40 ? 'text-amber-700' : 'text-red-700') }}">
                {{ $statsBulan['gross_margin'] }}%
            </p>
        </div>
    </div>
</div>

{{-- ── STATS HARI INI ───────────────────────────────────────────────── --}}
<div>
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Hari ini</p>
    <div class="grid grid-cols-4 gap-3">
        <div class="bg-white border border-gray-100 rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-2">Pendapatan</p>
            <p class="text-xl font-semibold text-gray-900">
                Rp {{ number_format($stats['pendapatan_hari_ini'], 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white border border-gray-100 rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-2">Gross Profit</p>
            <p class="text-xl font-semibold text-green-700">
                Rp {{ number_format($stats['gross_profit_hari'], 0, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Margin {{ $stats['gross_margin'] }}%</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-2">Dalam proses</p>
            <p class="text-xl font-semibold text-gray-900">{{ $stats['dalam_antrian'] }}</p>
            <p class="text-xs text-gray-400 mt-1">antri + proses</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-2">Siap diambil</p>
            <p class="text-xl font-semibold text-gray-900">{{ $stats['siap_diambil'] }}</p>
            <p class="text-xs text-gray-400 mt-1">menunggu pelanggan</p>
        </div>
    </div>
</div>

{{-- ── GRAFIK 7 HARI ────────────────────────────────────────────────── --}}
@php
    $harian7 = collect();
    for ($i = 6; $i >= 0; $i--) {
        $d     = now()->subDays($i)->toDateString();
        $found = $grafikHarian->firstWhere('tanggal', $d);
        $harian7->push(['tanggal' => $d, 'total' => $found ? (int)$found->total : 0]);
    }
    $maxHarian   = $harian7->max('total') ?: 1;
    $totalMinggu = $harian7->sum('total');
    $barMaxPx    = 96;
    $days_id     = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
@endphp
<div class="bg-white border border-gray-100 rounded-xl p-5"
     x-data="{ hov: null }">

    {{-- Header row --}}
    <div class="flex items-start justify-between mb-5">
        <div>
            <p class="text-sm font-semibold text-gray-900">Pendapatan 7 hari terakhir</p>
            <p class="text-xs text-gray-400 mt-0.5">
                Total: <span class="font-medium text-gray-700">Rp {{ number_format($totalMinggu, 0, ',', '.') }}</span>
            </p>
        </div>
        {{-- Hover tooltip --}}
        <div class="text-right transition-opacity duration-150"
             :class="hov ? 'opacity-100' : 'opacity-0'">
            <p class="text-sm font-semibold text-gray-900"
               x-text="hov ? 'Rp ' + Number(hov.total).toLocaleString('id-ID') : ' '"></p>
            <p class="text-xs text-gray-400" x-text="hov ? hov.label : ' '"></p>
        </div>
    </div>

    {{-- Chart --}}
    <div class="relative">

        {{-- Horizontal grid lines --}}
        <div class="absolute inset-x-0 pointer-events-none flex flex-col justify-between"
             style="top:0; height: {{ $barMaxPx }}px">
            @for($gi = 0; $gi < 5; $gi++)
            <div class="border-t {{ $gi === 4 ? 'border-gray-200' : 'border-dashed border-gray-100' }}"></div>
            @endfor
        </div>

        {{-- Bars + labels --}}
        <div class="flex items-end gap-1.5">
            @foreach($harian7 as $idx => $h)
            @php
                $barH    = $maxHarian > 0 ? max(3, round(($h['total'] / $maxHarian) * $barMaxPx)) : 3;
                $isToday = $h['tanggal'] === now()->toDateString();
                $dayLbl  = $days_id[\Carbon\Carbon::parse($h['tanggal'])->dayOfWeek];
                $dateLbl = \Carbon\Carbon::parse($h['tanggal'])->isoFormat('D MMM');
                $delay   = round($idx * 0.05, 2);
            @endphp
            <div class="flex flex-col items-center gap-1.5 flex-1 cursor-default"
                 @mouseenter="hov = { total: {{ $h['total'] }}, label: '{{ $dayLbl }}, {{ $dateLbl }}' }"
                 @mouseleave="hov = null">

                {{-- Bar --}}
                <div class="w-full rounded-t-sm overflow-hidden"
                     style="height: {{ $barH }}px">
                    <div class="w-full h-full rounded-t-sm"
                         :class="hov && hov.label === '{{ $dayLbl }}, {{ $dateLbl }}'
                             ? 'bg-brand'
                             : '{{ $isToday ? 'bg-brand' : ($h['total'] > 0 ? 'bg-brand/30' : 'bg-gray-100') }}'"
                         x-init="$el.style.transform = 'scaleY(0)';
                                 $el.style.transformOrigin = 'bottom';
                                 setTimeout(() => {
                                     $el.style.transition = 'transform 0.45s cubic-bezier(0.34,1.1,0.64,1)';
                                     $el.style.transform  = 'scaleY(1)';
                                 }, {{ $idx * 50 }})">
                    </div>
                </div>

                {{-- Day label --}}
                <span class="text-[10px] leading-none select-none
                    {{ $isToday ? 'text-brand font-semibold' : 'text-gray-400' }}">
                    {{ $dayLbl }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── TOP ITEMS (seperti foto 2) ──────────────────────────────────── --}}
@if($topItems->count() > 0)
<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Top Items</p>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Item</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Item Sold</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Gross Sales</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Net Sales</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Gross Profit</th>
            </tr>
        </thead>
        <tbody>
        @foreach($topItems as $item)
            <tr class="border-b border-gray-50 hover:bg-gray-50">
                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $item['nama'] }}</td>
                <td class="px-5 py-3 text-sm text-right text-gray-600">{{ $item['item_sold'] }}</td>
                <td class="px-5 py-3 text-sm text-right text-gray-700">
                    Rp {{ number_format($item['gross_sales'], 0, ',', '.') }}
                </td>
                <td class="px-5 py-3 text-sm text-right text-gray-700">
                    Rp {{ number_format($item['net_sales'], 0, ',', '.') }}
                </td>
                <td class="px-5 py-3 text-sm text-right font-semibold text-green-700">
                    Rp {{ number_format($item['gross_profit'], 0, ',', '.') }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── ORDER TERBARU ────────────────────────────────────────────────── --}}
<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
        <span class="text-sm font-semibold text-gray-900">Order terbaru</span>
        <a href="{{ route('orders.index') }}" class="text-xs text-gray-400 hover:text-gray-700">Lihat semua →</a>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full min-w-[640px]">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                @foreach(['No. order','Pelanggan','Layanan','Lokasi','Total','Profit','Status'] as $h)
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 whitespace-nowrap">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
        @forelse($orders_terbaru as $o)
            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
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
                <td class="px-4 py-3">
                    @if($o->lokasi)
                    <span class="text-xs font-mono font-bold bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded">
                        {{ $o->lokasi->kode }}
                    </span>
                    @else
                    <span class="text-xs text-gray-300">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs font-medium text-gray-900 whitespace-nowrap">
                    {{ $o->pembayaran?->total_format ?? '—' }}
                </td>
                <td class="px-4 py-3 text-xs font-medium text-green-700 whitespace-nowrap">
                    @if($o->hpp > 0)
                        Rp {{ number_format($o->gross_profit, 0, ',', '.') }}
                    @else
                        <span class="text-gray-300">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $o->status_badge['class'] }}">
                        {{ $o->status_badge['label'] }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400">Belum ada order.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>

</div>
@endsection

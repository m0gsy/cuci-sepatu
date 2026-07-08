@extends('layouts.app')
@section('title', 'Laporan Profit / Loss')

@section('header-actions')
<form method="GET" class="flex items-center gap-3">
    <input type="month" name="bulan" value="{{ $bulan }}"
           class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
           onchange="this.form.submit()">
</form>
<a href="{{ route('hpp.index') }}"
   class="text-xs border border-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
    Kelola HPP →
</a>
@endsection

@section('content')
<div class="space-y-5">

@php
    $namaBulan = \Carbon\Carbon::parse($bulan . '-01')->isoFormat('MMMM Y');
@endphp

{{-- ── SALES SUMMARY ────────────────────────────────────────────────── --}}
<div>
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Sales Summary — {{ $namaBulan }}</p>
    <div class="grid grid-cols-3 gap-3 mb-3">
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Gross Sales</p>
            <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($grossSales, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Net Sales</p>
            <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($netSales, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Gross Profit</p>
            <p class="text-2xl font-semibold text-green-700">Rp {{ number_format($grossProfit, 0, ',', '.') }}</p>
        </div>
    </div>
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Transactions</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $orders->count() }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Avg per Transaksi</p>
            <p class="text-2xl font-semibold text-gray-900">
                Rp {{ $orders->count() > 0 ? number_format(intdiv($netSales, $orders->count()), 0, ',', '.') : 0 }}
            </p>
        </div>
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Gross Margin</p>
            <p class="text-2xl font-semibold {{ $grossMargin >= 70 ? 'text-green-700' : ($grossMargin >= 40 ? 'text-amber-700' : 'text-red-700') }}">
                {{ $grossMargin }}%
            </p>
        </div>
    </div>
</div>

{{-- ── GROSS PROFIT BREAKDOWN (seperti foto 1) ──────────────────────── --}}
<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <p class="text-sm font-semibold text-gray-900">Gross Profit</p>
        <p class="text-xs text-gray-400 mt-0.5">Gross Profit = Net Sales − Cost of Goods Sold (HPP)</p>
    </div>
    <div class="px-5 py-4 space-y-3">
        <div class="flex items-center justify-between py-2 border-b border-gray-50">
            <span class="text-sm text-gray-700">Gross Sales</span>
            <span class="text-sm font-medium text-gray-900">Rp {{ number_format($grossSales, 0, ',', '.') }}</span>
        </div>
        @if($diskon > 0)
        <div class="flex items-center justify-between py-2 border-b border-gray-50">
            <span class="text-sm text-gray-700">Discounts</span>
            <span class="text-sm text-gray-500">(Rp {{ number_format($diskon, 0, ',', '.') }})</span>
        </div>
        @endif
        <div class="flex items-center justify-between py-2 border-b border-gray-200">
            <span class="text-sm font-semibold text-gray-900">Net Sales</span>
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($netSales, 0, ',', '.') }}</span>
                <span class="bg-blue-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">100%</span>
            </div>
        </div>
        <div class="flex items-center justify-between py-2 border-b border-gray-200">
            <span class="text-sm text-gray-700">Cost of Goods Sold (HPP)</span>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">(Rp {{ number_format($totalHpp, 0, ',', '.') }})</span>
                @php $hppPct = $netSales > 0 ? round(($totalHpp / $netSales) * 100) : 0; @endphp
                <span class="bg-red-500 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $hppPct }}%</span>
            </div>
        </div>
        <div class="flex items-center justify-between py-3 bg-gray-50 rounded-xl px-4">
            <span class="text-base font-bold text-gray-900">Gross Profit</span>
            <div class="flex items-center gap-3">
                <span class="text-base font-bold text-green-700">Rp {{ number_format($grossProfit, 0, ',', '.') }}</span>
                <span class="bg-green-500 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $grossMargin }}%</span>
            </div>
        </div>
    </div>
</div>

{{-- ── ITEM SUMMARY (seperti foto 2) ───────────────────────────────── --}}
<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <p class="text-sm font-semibold text-gray-900">Item Summary</p>
        <p class="text-xs text-gray-400 mt-0.5">Performa per layanan bulan ini</p>
    </div>

    {{-- Top Items --}}
    <div class="px-5 pt-3 pb-1">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Top Items</p>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Item</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Item Sold</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Gross Sales</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Net Sales</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">HPP</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Gross Profit</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Margin</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rekapLayanan as $item)
            <tr class="border-b border-gray-50 hover:bg-gray-50">
                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $item['nama'] }}</td>
                <td class="px-5 py-3 text-sm text-right text-gray-600">{{ $item['item_sold'] }}</td>
                <td class="px-5 py-3 text-sm text-right text-gray-700">
                    Rp. {{ number_format($item['gross_sales'], 0, '.', '.') }}
                </td>
                <td class="px-5 py-3 text-sm text-right text-gray-700">
                    Rp. {{ number_format($item['net_sales'], 0, '.', '.') }}
                </td>
                <td class="px-5 py-3 text-sm text-right text-red-600">
                    (Rp. {{ number_format($item['hpp'], 0, '.', '.') }})
                </td>
                <td class="px-5 py-3 text-sm text-right font-semibold text-green-700">
                    Rp. {{ number_format($item['gross_profit'], 0, '.', '.') }}
                </td>
                <td class="px-5 py-3 text-right">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                 {{ $item['margin'] >= 70 ? 'bg-green-100 text-green-800' :
                                   ($item['margin'] >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                        {{ $item['margin'] }}%
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-5 py-8 text-center text-sm text-gray-400">
                    Belum ada data bulan ini.
                </td>
            </tr>
        @endforelse
        </tbody>
        @if($rekapLayanan->count() > 0)
        <tfoot>
            <tr class="bg-gray-50 border-t border-gray-200">
                <td class="px-5 py-3 text-sm font-bold text-gray-900">TOTAL</td>
                <td class="px-5 py-3 text-sm text-right font-bold">{{ $rekapLayanan->sum('item_sold') }}</td>
                <td class="px-5 py-3 text-sm text-right font-bold">
                    Rp. {{ number_format($rekapLayanan->sum('gross_sales'), 0, '.', '.') }}
                </td>
                <td class="px-5 py-3 text-sm text-right font-bold">
                    Rp. {{ number_format($rekapLayanan->sum('net_sales'), 0, '.', '.') }}
                </td>
                <td class="px-5 py-3 text-sm text-right font-bold text-red-700">
                    (Rp. {{ number_format($rekapLayanan->sum('hpp'), 0, '.', '.') }})
                </td>
                <td class="px-5 py-3 text-sm text-right font-bold text-green-700">
                    Rp. {{ number_format($rekapLayanan->sum('gross_profit'), 0, '.', '.') }}
                </td>
                <td class="px-5 py-3 text-right">
                    <span class="text-sm font-bold {{ $grossMargin >= 70 ? 'text-green-700' : 'text-amber-700' }}">
                        {{ $grossMargin }}%
                    </span>
                </td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- Rekap per lokasi --}}
    @if($rekapLokasi->count() > 0)
    <div class="border-t border-gray-100 px-5 pt-3 pb-1 mt-2">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Sales by Location</p>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Lokasi</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Jumlah Order</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Net Sales</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">HPP</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Gross Profit</th>
            </tr>
        </thead>
        <tbody>
        @foreach($rekapLokasi as $lok)
            <tr class="border-b border-gray-50 hover:bg-gray-50">
                <td class="px-5 py-3">
                    <p class="text-sm font-medium text-gray-900">{{ $lok['nama'] }}</p>
                    <p class="text-xs text-gray-400">{{ $lok['kode'] }}</p>
                </td>
                <td class="px-5 py-3 text-sm text-right text-gray-600">{{ $lok['jumlah_order'] }}</td>
                <td class="px-5 py-3 text-sm text-right text-gray-700">Rp. {{ number_format($lok['net_sales'], 0, '.', '.') }}</td>
                <td class="px-5 py-3 text-sm text-right text-red-600">(Rp. {{ number_format($lok['hpp'], 0, '.', '.') }})</td>
                <td class="px-5 py-3 text-sm text-right font-semibold text-green-700">Rp. {{ number_format($lok['gross_profit'], 0, '.', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- ── DETAIL ORDER ─────────────────────────────────────────────────── --}}
<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <p class="text-sm font-semibold text-gray-900">Detail order — {{ $orders->count() }} transaksi</p>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full min-w-[720px]">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 whitespace-nowrap">No. order</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Pelanggan</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Layanan</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Lokasi</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 whitespace-nowrap">Net Sales</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 whitespace-nowrap">HPP</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 whitespace-nowrap">Gross Profit</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">Margin</th>
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
                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $o->lokasi?->kode ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-900 whitespace-nowrap">
                    Rp {{ number_format($o->net_sales, 0, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-sm text-right text-red-600 whitespace-nowrap">
                    ({{ number_format($o->hpp, 0, ',', '.') }})
                </td>
                <td class="px-4 py-3 text-sm text-right font-medium text-green-700 whitespace-nowrap">
                    Rp {{ number_format($o->gross_profit, 0, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    <span class="text-xs font-semibold {{ $o->gross_margin >= 70 ? 'text-green-700' : ($o->gross_margin >= 40 ? 'text-amber-700' : 'text-red-700') }}">
                        {{ $o->gross_margin }}%
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-400">Belum ada data.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>

</div>
@endsection

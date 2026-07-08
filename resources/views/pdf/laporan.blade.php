<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'DejaVu Sans', 'Arial', sans-serif;
    font-size: 11px;
    color: #111;
    background: #fff;
    padding: 20mm 18mm;
}

.header-row { display: table; width: 100%; margin-bottom: 6mm; }
.header-left, .header-right { display: table-cell; vertical-align: bottom; }
.header-right { text-align: right; }

h1 { font-size: 20px; font-weight: bold; color: #111; }
.subtitle { font-size: 10px; color: #888; margin-top: 2px; }

.divider-thick { border: none; border-top: 2px solid #111; margin-bottom: 6mm; }
.divider-thin  { border: none; border-top: 0.5px solid #e0e0e0; margin: 5mm 0; }

/* Metric cards */
.metrics { display: table; width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 8mm; }
.metric  { display: table-cell; background: #f7f7f5; border-radius: 6px; padding: 5mm 6mm; }
.metric-label { font-size: 9px; color: #888; margin-bottom: 2px; }
.metric-value { font-size: 18px; font-weight: bold; color: #111; }

/* Section title */
.section-title {
    font-size: 12px;
    font-weight: bold;
    color: #111;
    margin-bottom: 3mm;
    padding-bottom: 2mm;
    border-bottom: 0.5px solid #e0e0e0;
}

/* Tables */
table.data {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8mm;
}
table.data thead tr {
    background: #111;
    color: #fff;
}
table.data thead td {
    padding: 3mm 4mm;
    font-size: 9px;
    font-weight: bold;
}
table.data tbody tr:nth-child(even) { background: #f9f9f7; }
table.data tbody td {
    padding: 2.5mm 4mm;
    font-size: 10px;
    border-bottom: 0.3px solid #eee;
    color: #111;
}
table.data tfoot tr {
    background: #333;
    color: #fff;
}
table.data tfoot td {
    padding: 3mm 4mm;
    font-size: 10px;
    font-weight: bold;
}

.right { text-align: right; }
.center { text-align: center; }
.muted  { color: #888; }

/* Status badges */
.badge {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 8px;
    font-weight: bold;
}
.badge-draft               { background: #F3F4F6; color: #475569; }
.badge-menunggu_pembayaran { background: #FEF3C7; color: #92400E; }
.badge-diproses            { background: #DBEAFE; color: #1E40AF; }
.badge-siap_diambil        { background: #D1FAE5; color: #065F46; }
.badge-selesai             { background: #E0F2FE; color: #0369A1; }
.badge-batal               { background: #FEE2E2; color: #B91C1C; }

.footer {
    margin-top: 10mm;
    font-size: 9px;
    color: #bbb;
    text-align: center;
    border-top: 0.5px solid #eee;
    padding-top: 4mm;
}
</style>
</head>
<body>

{{-- Header --}}
<div class="header-row">
    <div class="header-left">
        <h1>Laporan Penjualan</h1>
        <div class="subtitle">Step Shine Works &nbsp;·&nbsp; Periode: {{ \Carbon\Carbon::parse($bulan . '-01')->isoFormat('MMMM Y') }}</div>
    </div>
    <div class="header-right">
        <div style="font-size:10px;color:#888;">Dicetak {{ now()->isoFormat('D MMM Y, HH:mm') }}</div>
    </div>
</div>

<hr class="divider-thick">

{{-- Metric cards --}}
@php
    $totalOrder = $rekap->sum('orders_count');
    $rata2 = $totalOrder > 0 ? intdiv($total_bulan, $totalOrder) : 0;
@endphp
<div class="metrics">
    <div class="metric">
        <div class="metric-label">Total pendapatan</div>
        <div class="metric-value">Rp {{ number_format($total_bulan, 0, ',', '.') }}</div>
    </div>
    <div class="metric" style="padding-left:6mm;">
        <div class="metric-label">Total order</div>
        <div class="metric-value">{{ $totalOrder }}</div>
    </div>
    <div class="metric" style="padding-left:6mm;">
        <div class="metric-label">Rata-rata per order</div>
        <div class="metric-value">Rp {{ number_format($rata2, 0, ',', '.') }}</div>
    </div>
    <div class="metric" style="padding-left:6mm;">
        <div class="metric-label">Jenis layanan</div>
        <div class="metric-value">{{ $rekap->count() }}</div>
    </div>
</div>

{{-- Rekap per layanan --}}
<div class="section-title">Rekap per layanan</div>
<table class="data">
    <thead>
        <tr>
            <td>Layanan</td>
            <td class="right">Harga dasar</td>
            <td class="center">Jumlah order</td>
            <td class="right">Total pendapatan</td>
        </tr>
    </thead>
    <tbody>
    @foreach($rekap as $l)
        <tr>
            <td>{{ $l->nama }}</td>
            <td class="right muted">Rp {{ number_format($l->harga, 0, ',', '.') }}</td>
            <td class="center">{{ $l->orders_count }}</td>
            <td class="right" style="font-weight:bold;">
                Rp {{ number_format($l->total_pendapatan ?? 0, 0, ',', '.') }}
            </td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">Total</td>
            <td class="center">{{ $totalOrder }}</td>
            <td class="right">Rp {{ number_format($total_bulan, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

{{-- Detail order --}}
<div class="section-title">Detail order &mdash; {{ $orders->count() }} transaksi</div>
<table class="data">
    <thead>
        <tr>
            <td>No. order</td>
            <td>Pelanggan</td>
            <td>Layanan</td>
            <td class="right">Total</td>
            <td class="center">Bayar</td>
            <td class="center">Status</td>
        </tr>
    </thead>
    <tbody>
    @foreach($orders as $o)
        <tr>
            <td style="font-family:monospace;font-size:9px;color:#666;">
                {{ $o->no_order }}
            </td>
            <td>{{ $o->nama_pelanggan }}</td>
            <td class="muted">
                {{ $o->items->isNotEmpty()
                    ? $o->items->map(fn($i) => $i->layanan->nama ?? '—')->join(', ')
                    : ($o->layanan->nama ?? '—') }}
            </td>
            <td class="right" style="font-weight:bold;">
                Rp {{ number_format($o->pembayaran?->total ?? 0, 0, ',', '.') }}
            </td>
            <td class="center">
                <span style="color: {{ $o->pembayaran?->status === 'lunas' ? '#065F46' : '#92400E' }}; font-size:9px;">
                    {{ ucfirst($o->pembayaran?->status ?? '-') }}
                </span>
            </td>
            <td class="center">
                <span class="badge badge-{{ $o->status }}">
                    {{ ucfirst($o->status) }}
                </span>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    Laporan ini dibuat otomatis oleh sistem pencatatan Step Shine Works.
    Data berdasarkan transaksi yang tercatat pada periode bersangkutan.
</div>

</body>
</html>

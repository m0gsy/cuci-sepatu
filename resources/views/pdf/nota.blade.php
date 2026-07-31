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
    width: 72mm;
    padding: 6mm 5mm;
}

.center   { text-align: center; }
.right    { text-align: right; }
.bold     { font-weight: bold; }
.muted    { color: #888; font-size: 10px; }
.divider  { border: none; border-top: 1px dashed #ccc; margin: 4mm 0; }
.divider-solid { border: none; border-top: 1.5px solid #111; margin: 4mm 0; }

.shop-name  { font-size: 16px; font-weight: bold; letter-spacing: 1px; }
.shop-info  { font-size: 9px; color: #666; margin-top: 1mm; }

.no-order-label { font-size: 9px; color: #888; margin-bottom: 1mm; }
.no-order-value { font-size: 15px; font-weight: bold; letter-spacing: 2px; }

table.info { width: 100%; border-collapse: collapse; }
table.info td { padding: 1.5mm 0; vertical-align: top; }
table.info td:first-child { color: #888; width: 26mm; font-size: 10px; }
table.info td:last-child  { font-size: 10.5px; }

table.calc { width: 100%; border-collapse: collapse; margin-bottom: 3mm; }
table.calc td { padding: 1mm 0; }
table.calc td:last-child { text-align: right; }

.total-box {
    background: #f5f5f5;
    border-radius: 4px;
    padding: 3mm 4mm;
    margin-top: 2mm;
}
.total-label { font-size: 9px; color: #888; margin-bottom: 1mm; }
.total-value { font-size: 17px; font-weight: bold; }
.total-meta  { font-size: 9px; color: #666; margin-top: 1mm; }

.status-badge {
    display: inline-block;
    padding: 1.5mm 4mm;
    border-radius: 20px;
    font-size: 10px;
    font-weight: bold;
}

.footer {
    font-size: 9px;
    color: #aaa;
    text-align: center;
    line-height: 1.6;
}
</style>
</head>
<body>

<div class="center">
    <div class="shop-name">Step Shine Works</div>
    <div class="shop-info">Jl. Boulevard Barat L10, Perumahan Duta Harapan No.Kelurahan, RT.001/RW.013, Harapan Baru, Kec. Bekasi Utara, Kota Bks, Jawa Barat 17123</div>
    <div class="shop-info">0819-5880-0679</div>
</div>

<hr class="divider-solid" style="margin-top: 4mm;">

<div class="center" style="margin-bottom: 3mm;">
    <div class="no-order-label">Nomor Order</div>
    <div class="no-order-value">{{ $order->no_order }}</div>
    <div class="muted" style="margin-top: 1mm;">{{ $order->created_at->isoFormat('D MMMM Y, HH:mm') }}</div>
</div>

<hr class="divider">

<table class="info">
    <tr>
        <td>Pelanggan</td>
        <td class="bold">{{ $order->nama_pelanggan }}</td>
    </tr>
    <tr>
        <td>No. HP</td>
        <td>{{ $order->no_hp_display }}</td>
    </tr>
    <tr>
        <td>Estimasi</td>
        <td>{{ $order->estimasi_selesai?->isoFormat('D MMMM Y, HH:mm') ?? '—' }}</td>
    </tr>
    @if($order->catatan)
    <tr>
        <td>Catatan</td>
        <td style="color: #555;">{{ $order->catatan }}</td>
    </tr>
    @endif
</table>

<hr class="divider">

<table class="calc" style="width:100%; border-bottom: 1px dashed #ccc; padding-bottom: 2mm; margin-bottom: 2mm;">
    <tr class="bold" style="font-size: 8px; color: #888;">
        <td style="width: 45%;">Layanan / Sepatu</td>
        <td style="width: 15%; text-align: center;">Jumlah</td>
        <td style="width: 40%; text-align: right;">Total</td>
    </tr>
    @forelse($order->items as $item)
    <tr>
        <td style="padding: 1.5mm 0; font-size: 10px;">
            <span class="bold">{{ $item->layanan->nama }}</span>
            <br><span class="muted">{{ $item->jenisBarang->nama }} · {{ $item->merek ?? '—' }} ({{ $item->warna ?? '—' }})</span>
        </td>
        <td style="text-align: center; padding: 1.5mm 0; font-size: 10.5px;">{{ $item->jumlah_pasang }}</td>
        <td style="text-align: right; padding: 1.5mm 0; font-size: 10.5px;">Rp {{ number_format($item->harga_satuan * $item->jumlah_pasang, 0, ',', '.') }}</td>
    </tr>
    @empty
    {{-- Fallback untuk order lama (sebelum sistem multi-item) --}}
    @if($order->layanan_id || $order->jenis_sepatu || $order->jumlah_pasang)
    <tr>
        <td style="padding: 1.5mm 0; font-size: 10px;">
            <span class="bold">{{ $order->layanan->nama ?? '—' }}</span>
            <br><span class="muted">{{ $order->jenis_sepatu ?? '—' }} · {{ $order->merek ?? '—' }} ({{ $order->warna ?? '—' }})</span>
        </td>
        <td style="text-align: center; padding: 1.5mm 0; font-size: 10.5px;">{{ $order->jumlah_pasang ?? 0 }}</td>
        <td style="text-align: right; padding: 1.5mm 0; font-size: 10.5px;">Rp {{ number_format(($order->harga_satuan ?? 0) * ($order->jumlah_pasang ?? 0), 0, ',', '.') }}</td>
    </tr>
    @else
    <tr>
        <td colspan="3" style="padding: 3mm 0; text-align: center; color: #aaa; font-size: 10px;">Belum ada item sepatu.</td>
    </tr>
    @endif
    @endforelse
</table>

@if($order->diskon > 0)
<table class="calc" style="margin-top: 1mm; margin-bottom: 1mm;">
    <tr>
        <td class="muted">Diskon Voucher ({{ $order->voucher->kode ?? '' }})</td>
        <td class="right" style="color: green; font-size: 10.5px;">- Rp {{ number_format($order->diskon, 0, ',', '.') }}</td>
    </tr>
</table>
@endif

@if(($order->diskon_poin ?? 0) > 0)
<table class="calc" style="margin-top: 1mm; margin-bottom: 1mm;">
    <tr>
        <td class="muted">Diskon Poin ({{ $order->poin_digunakan }} poin)</td>
        <td class="right" style="color: green; font-size: 10.5px;">- Rp {{ number_format($order->diskon_poin, 0, ',', '.') }}</td>
    </tr>
</table>
@endif

<div class="total-box">
    <div class="total-label">Total</div>
    <div class="total-value">Rp {{ number_format($order->pembayaran?->total ?? 0, 0, ',', '.') }}</div>
    <div class="total-meta">
        Metode: {{ strtoupper($order->pembayaran?->metode ?? 'tempo') }}
        &nbsp;·&nbsp;
        <span class="bold">
            {{ $order->pembayaran?->status === 'selesai' ? 'SELESAI' : 'BELUM SELESAI' }}
        </span>
    </div>
</div>

<div class="center" style="margin: 3mm 0;">
    <span class="status-badge" style="background: #f1f5f9; color: #475569; font-size: 9px; padding: 1mm 3mm; border-radius: 4px;">
        STATUS: {{ strtoupper(str_replace('_', ' ', $order->status)) }}
    </span>
</div>

<div class="center" style="margin: 2mm 0; font-size: 9px; color: #334155; background: #f8fafc; padding: 2mm; border-radius: 4px; border: 1px solid #e2e8f0;">
    <div style="font-weight: bold;">Pembayaran Transfer BCA:</div>
    <div style="font-size: 11px; font-weight: bold; letter-spacing: 0.5px;">0661625936</div>
    <div>a.n. Muhammad Irfan Kanugrahan</div>
</div>

<hr class="divider">

<div class="footer">
    <p>Terima kasih telah mempercayakan</p>
    <p>sepatu Anda kepada kami.</p>
    <p style="margin-top: 2mm;">Simpan nota ini untuk pengambilan.</p>
    <p style="margin-top: 3mm; font-size: 8px; color: #ccc;">
        Dicetak {{ now()->isoFormat('D MMM Y HH:mm') }}
        &nbsp;·&nbsp; {{ auth()->user()->name ?? 'Sistem' }}
    </p>
</div>

</body>
</html>

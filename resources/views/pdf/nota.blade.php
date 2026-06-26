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
.status-antri   { background: #FEF3C7; color: #92400E; }
.status-proses  { background: #DBEAFE; color: #1E40AF; }
.status-selesai { background: #D1FAE5; color: #065F46; }
.status-diambil { background: #F3F4F6; color: #374151; }

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
        <td>{{ $order->no_hp }}</td>
    </tr>
    <tr>
        <td>Jenis</td>
        <td>{{ $order->jenis_sepatu }}</td>
    </tr>
    <tr>
        <td>Jumlah</td>
        <td>{{ $order->jumlah_pasang }} Pasang</td>
    </tr>
    <tr>
        <td>Layanan</td>
        <td class="bold">{{ $order->layanan->nama }}</td>
    </tr>
    <tr>
        <td>Estimasi</td>
        <td>{{ $order->estimasi_selesai->isoFormat('D MMMM Y') }}</td>
    </tr>
    @if($order->catatan)
    <tr>
        <td>Catatan</td>
        <td style="color: #555;">{{ $order->catatan }}</td>
    </tr>
    @endif
</table>

<hr class="divider">

<table class="calc">
    <tr>
        <td class="muted">Harga satuan</td>
        <td>Rp {{ number_format($order->harga_efektif, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td class="muted">Jumlah Pasang</td>
        <td>× {{ $order->jumlah_pasang }}</td>
    </tr>
</table>

<div class="total-box">
    <div class="total-label">Total</div>
    <div class="total-value">Rp {{ number_format($order->pembayaran?->total ?? 0, 0, ',', '.') }}</div>
    <div class="total-meta">
        Metode: {{ ucfirst($order->pembayaran->metode) }}
        &nbsp;·&nbsp;
        <span class="{{ $order->pembayaran->status === 'lunas' ? '' : 'bold' }}">
            {{ strtoupper($order->pembayaran->status) }}
        </span>
    </div>
</div>

<div class="center" style="margin: 3mm 0;">
    <span class="status-badge status-{{ $order->status }}">
        Status: {{ strtoupper($order->status) }}
    </span>
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

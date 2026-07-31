<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $order->no_order }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
        font-size: 12px;
        color: #1e293b;
        background: #ffffff;
        padding: 30px;
    }
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 25px;
        border-bottom: 2px solid #0f172a;
        padding-bottom: 15px;
    }
    .header-table td { vertical-align: top; }
    .brand-title {
        font-size: 22px;
        font-weight: bold;
        color: #0f172a;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .brand-sub {
        font-size: 10px;
        color: #64748b;
        margin-top: 4px;
        line-height: 1.4;
    }
    .invoice-title {
        font-size: 24px;
        font-weight: bold;
        color: #2563eb;
        text-align: right;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .invoice-no {
        font-size: 13px;
        font-weight: bold;
        color: #334155;
        text-align: right;
        margin-top: 4px;
    }
    .meta-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 25px;
    }
    .meta-table td {
        width: 50%;
        vertical-align: top;
    }
    .info-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 12px 15px;
    }
    .info-box-title {
        font-size: 10px;
        font-weight: bold;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 6px;
        letter-spacing: 0.5px;
    }
    .info-row {
        margin-bottom: 4px;
        font-size: 11px;
    }
    .info-row span.label {
        color: #64748b;
        display: inline-block;
        width: 100px;
    }
    .info-row span.val {
        font-weight: bold;
        color: #0f172a;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 25px;
    }
    .items-table th {
        background: #0f172a;
        color: #ffffff;
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        padding: 8px 10px;
        text-align: left;
        letter-spacing: 0.5px;
    }
    .items-table td {
        padding: 10px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 11px;
        vertical-align: top;
    }
    .items-table tr:nth-child(even) td {
        background: #f8fafc;
    }
    .item-name {
        font-weight: bold;
        color: #0f172a;
        font-size: 11px;
    }
    .item-sub {
        font-size: 10px;
        color: #64748b;
        margin-top: 2px;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }

    .summary-table {
        width: 100%;
        border-collapse: collapse;
    }
    .summary-table td.col-left {
        width: 55%;
        vertical-align: top;
        padding-right: 20px;
    }
    .summary-table td.col-right {
        width: 45%;
        vertical-align: top;
    }
    .calc-table {
        width: 100%;
        border-collapse: collapse;
    }
    .calc-table td {
        padding: 6px 0;
        font-size: 11px;
    }
    .calc-table td.label {
        color: #64748b;
    }
    .calc-table td.val {
        text-align: right;
        font-weight: bold;
        color: #0f172a;
    }
    .calc-table tr.total-row td {
        border-top: 2px solid #0f172a;
        padding-top: 10px;
        font-size: 14px;
    }
    .calc-table tr.total-row td.val {
        color: #2563eb;
        font-size: 16px;
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-success { background: #dcfce7; color: #166534; }
    .badge-warning { background: #fef9c3; color: #854d0e; }
    .badge-info    { background: #e0f2fe; color: #075985; }

    .footer {
        margin-top: 40px;
        border-top: 1px solid #e2e8f0;
        padding-top: 15px;
        text-align: center;
        font-size: 10px;
        color: #94a3b8;
    }
</style>
</head>
<body>

<table class="header-table">
    <tr>
        <td>
            <div class="brand-title">Step Shine Works</div>
            <div class="brand-sub">
                Jl. Boulevard Barat L10, Harapan Baru, Bekasi Utara, Kota Bekasi, Jawa Barat 17123<br>
                Telp / Kontak: 0819-5880-0679
            </div>
        </td>
        <td>
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-no"># {{ $order->no_order }}</div>
            <div class="brand-sub text-right" style="margin-top: 6px;">
                Tanggal: {{ $order->created_at->isoFormat('D MMMM Y') }}
            </div>
        </td>
    </tr>
</table>

<table class="meta-table">
    <tr>
        <td style="padding-right: 10px;">
            <div class="info-box">
                <div class="info-box-title">Pelanggan</div>
                <div class="info-row"><span class="label">Nama:</span> <span class="val">{{ $order->nama_pelanggan }}</span></div>
                <div class="info-row"><span class="label">No. HP:</span> <span class="val">{{ $order->no_hp_display ?? $order->no_hp }}</span></div>
                @if($order->lokasi)
                <div class="info-row"><span class="label">Rak / Lokasi:</span> <span class="val">{{ $order->lokasi->nama }}</span></div>
                @endif
            </div>
        </td>
        <td style="padding-left: 10px;">
            <div class="info-box">
                <div class="info-box-title">Detail Order</div>
                <div class="info-row">
                    <span class="label">Status Order:</span>
                    <span class="badge badge-info">{{ strtoupper(str_replace('_', ' ', $order->status)) }}</span>
                </div>
                <div class="info-row" style="margin-top: 4px;">
                    <span class="label">Estimasi Selesai:</span>
                    <span class="val">{{ $order->estimasi_selesai?->isoFormat('D MMMM Y, HH:mm') ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Pembayaran:</span>
                    @if($order->pembayaran?->status === 'selesai')
                        <span class="badge badge-success">LUNAS</span>
                    @else
                        <span class="badge badge-warning">BELUM LUNAS</span>
                    @endif
                </div>
            </div>
        </td>
    </tr>
</table>

<table class="items-table">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 45%;">Layanan & Item</th>
            <th style="width: 15%;" class="text-center">Jumlah</th>
            <th style="width: 15%;" class="text-right">Harga Satuan</th>
            <th style="width: 20%;" class="text-right">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @forelse($order->items as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>
                <div class="item-name">{{ $item->layanan->nama }}</div>
                <div class="item-sub">
                    Jenis: {{ $item->jenisBarang->nama }}
                    @if($item->merek || $item->warna)
                        · {{ $item->merek ?? '-' }} ({{ $item->warna ?? '-' }})
                    @endif
                    @if($item->kondisi)
                        · Catatan: {{ $item->kondisi }}
                    @endif
                </div>
            </td>
            <td class="text-center">{{ $item->jumlah_pasang }} pasang</td>
            <td class="text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format($item->harga_satuan * $item->jumlah_pasang, 0, ',', '.') }}</td>
        </tr>
        @empty
        @if($order->layanan_id || $order->jenis_sepatu)
        <tr>
            <td class="text-center">1</td>
            <td>
                <div class="item-name">{{ $order->layanan->nama ?? '—' }}</div>
                <div class="item-sub">Jenis: {{ $order->jenis_sepatu ?? '—' }} · {{ $order->merek ?? '-' }} ({{ $order->warna ?? '-' }})</div>
            </td>
            <td class="text-center">{{ $order->jumlah_pasang ?? 1 }} pasang</td>
            <td class="text-right">Rp {{ number_format($order->harga_satuan ?? 0, 0, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format(($order->harga_satuan ?? 0) * ($order->jumlah_pasang ?? 1), 0, ',', '.') }}</td>
        </tr>
        @else
        <tr>
            <td colspan="5" class="text-center" style="color: #94a3b8; padding: 15px;">Belum ada item layanan.</td>
        </tr>
        @endif
        @endforelse
    </tbody>
</table>

<table class="summary-table">
    <tr>
        <td class="col-left">
            @if($order->catatan)
            <div class="info-box" style="margin-bottom: 10px;">
                <div class="info-box-title">Catatan Order</div>
                <div style="font-size: 11px; color: #334155;">{{ $order->catatan }}</div>
            </div>
            @endif
            <div class="info-box">
                <div class="info-box-title">Metode Pembayaran</div>
                <div style="font-size: 11px; color: #334155; text-transform: uppercase; font-weight: bold;">
                    {{ $order->pembayaran?->metode ?? 'tempo' }}
                </div>
                @if($order->pembayaran?->dibayar_pada)
                <div class="item-sub" style="margin-top: 4px;">
                    Dibayar pada: {{ $order->pembayaran->dibayar_pada->isoFormat('D MMMM Y, HH:mm') }}
                </div>
                @endif
            </div>
            <div class="info-box" style="margin-top: 10px;">
                <div class="info-box-title">Rekening Pembayaran Bank</div>
                <div style="font-size: 11px; color: #0f172a; font-weight: bold;">
                    BCA: 0661625936
                </div>
                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                    a.n. Muhammad Irfan Kanugrahan
                </div>
            </div>
        </td>
        <td class="col-right">
            <table class="calc-table">
                @php
                    $subtotal = $order->items->sum(fn($i) => $i->harga_satuan * $i->jumlah_pasang);
                    if ($subtotal == 0 && ($order->harga_satuan ?? 0) > 0) {
                        $subtotal = ($order->harga_satuan ?? 0) * ($order->jumlah_pasang ?? 1);
                    }
                @endphp
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="val">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($order->diskon > 0)
                <tr>
                    <td class="label">Diskon Voucher ({{ $order->voucher->kode ?? '' }})</td>
                    <td class="val" style="color: #16a34a;">- Rp {{ number_format($order->diskon, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if(($order->diskon_poin ?? 0) > 0)
                <tr>
                    <td class="label">Diskon Poin ({{ $order->poin_digunakan }} poin)</td>
                    <td class="val" style="color: #16a34a;">- Rp {{ number_format($order->diskon_poin, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td class="label" style="font-weight: bold; color: #0f172a;">TOTAL TAGIHAN</td>
                    <td class="val">Rp {{ number_format($order->pembayaran?->total ?? $subtotal, 0, ',', '.') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="footer">
    <p>Terima kasih telah mempercayakan perawatan sepatu Anda kepada Step Shine Works.</p>
    <p style="margin-top: 4px;">Dokumen ini merupakan bukti transaksi sah dari sistem Step Shine Works.</p>
</div>

</body>
</html>

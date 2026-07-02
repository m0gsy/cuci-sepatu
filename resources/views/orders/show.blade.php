@extends('layouts.app')
@section('title', 'Order ' . $order->no_order)

@section('header-actions')
    @if(auth()->user()->hasPermission('orders.manage'))
    <a href="{{ route('orders.edit', $order) }}"
       class="text-xs border border-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
        Edit order
    </a>
    <a href="{{ route('orders.nota', $order) }}" target="_blank"
       class="text-xs border border-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
        Cetak nota ↗
    </a>
    <form method="POST" action="{{ route('orders.invoice', $order) }}" class="inline">
        @csrf
        <button type="submit"
                class="text-xs bg-brand text-white px-4 py-2 rounded-lg hover:bg-brand-hover transition-colors">
            Kirim invoice WA
        </button>
    </form>
    <form method="POST" action="{{ route('orders.wa', $order) }}" class="inline">
        @csrf
        <button type="submit"
                class="text-xs border border-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
            Kirim ulang WA
        </button>
    </form>
    @endif
    <a href="{{ route('orders.index') }}" class="text-xs text-gray-400 hover:text-gray-700">← Kembali</a>
@endsection

@section('content')
<div class="max-w-3xl space-y-5">

    {{-- Info order --}}
    <div class="bg-white border border-gray-100 rounded-xl p-6">
        <div class="flex items-start justify-between mb-5">
            <div>
                <p class="text-xs text-gray-400 font-mono mb-1">{{ $order->no_order }}</p>
                @if($order->pelanggan)
                <div class="flex items-center gap-2">
                    <a href="{{ route('pelanggans.show', $order->pelanggan) }}"
                       class="text-lg font-semibold text-gray-900 hover:underline">
                        {{ $order->nama_pelanggan }}
                    </a>
                    @if($order->pelanggan->tier !== 'reguler')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $order->pelanggan->tier_badge }}">
                        {{ ucfirst($order->pelanggan->tier) }}
                    </span>
                    @endif
                </div>
                @else
                <p class="text-lg font-semibold text-gray-900">{{ $order->nama_pelanggan }}</p>
                @endif
                <p class="text-sm text-gray-500">{{ $order->no_hp }}</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $order->status_badge['class'] }}">
                {{ $order->status_badge['label'] }}
            </span>
        </div>

        <div class="border-t border-gray-100 pt-4">
            <dl class="grid grid-cols-2 gap-x-8 gap-y-3">
                @php
                    $fields = [
                        'Layanan'          => $order->layanan->nama,
                        'Jenis sepatu'     => $order->jenis_sepatu,
                    ];
                    if ($order->merek)  $fields['Merek']  = $order->merek;
                    if ($order->warna)  $fields['Warna']  = $order->warna;
                    if ($order->kondisi) $fields['Kondisi awal'] = $order->kondisi;
                    $fields['Jumlah pasang'] = $order->jumlah_pasang . ' pasang';
                    $fields['Masuk']         = $order->created_at->isoFormat('D MMM Y, HH:mm');
                    $fields['Est. selesai']  = $order->estimasi_selesai->isoFormat('D MMMM Y')
                                              . ($order->terlambat ? ' ⚠' : '');
                    $fields['Diinput oleh']  = $order->user->name;
                    if ($order->selesai_pada) {
                        $fields['Siap diambil'] = $order->selesai_pada->isoFormat('D MMM Y, HH:mm');
                    }
                @endphp
                @foreach($fields as $label => $value)
                <div>
                    <dt class="text-xs text-gray-400 mb-0.5">{{ $label }}</dt>
                    <dd class="text-sm font-medium {{ str_contains($value, '⚠') ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $value }}
                    </dd>
                </div>
                @endforeach
            </dl>

            @if($order->catatan)
            <div class="mt-4 bg-gray-50 rounded-lg px-4 py-3">
                <p class="text-xs text-gray-400 mb-1">Catatan kondisi</p>
                <p class="text-sm text-gray-700">{{ $order->catatan }}</p>
            </div>
            @endif

            @if($order->voucher)
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-400">Voucher:</span>
                <span class="font-mono text-xs font-semibold text-green-700 bg-green-50 px-2 py-0.5 rounded">
                    {{ $order->voucher->kode }}
                </span>
                <span class="text-xs text-green-700">
                    — diskon Rp {{ number_format($order->diskon, 0, ',', '.') }}
                </span>
            </div>
            @endif

            @if($order->token_publik)
            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center gap-3">
                <p class="text-xs text-gray-400">Link tracking pelanggan:</p>
                <a href="{{ route('status.order', $order->token_publik) }}" target="_blank"
                   class="text-xs text-blue-600 hover:underline font-mono">
                    /status/{{ substr($order->token_publik, 0, 8) }}...
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Lokasi sepatu --}}
    <div class="bg-white border border-gray-100 rounded-xl p-6" x-data="{ editLokasi: false }">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Lokasi sepatu</h2>
            @if(!$order->isSudahSelesai())
            <button @click="editLokasi = !editLokasi"
                    class="text-xs text-gray-400 hover:text-gray-700 border border-gray-200 px-3 py-1 rounded-lg">
                {{ $order->lokasi ? 'Ubah lokasi' : 'Set lokasi' }}
            </button>
            @endif
        </div>
        <div x-show="!editLokasi">
            @if($order->lokasi)
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-mono font-bold bg-gray-100 text-gray-800">
                    {{ $order->lokasi->kode }}
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $order->lokasi->nama }}</p>
                    @if($order->catatan_lokasi)
                    <p class="text-xs text-gray-500">{{ $order->catatan_lokasi }}</p>
                    @endif
                </div>
            </div>
            @else
            <p class="text-sm text-gray-400">Belum ada lokasi yang ditetapkan.</p>
            @endif
        </div>
        <div x-show="editLokasi">
            <form method="POST" action="{{ route('orders.lokasi', $order) }}">
                @csrf @method('PATCH')
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Lokasi</label>
                        <select name="lokasi_id"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            <option value="">-- Tanpa lokasi --</option>
                            @foreach($lokasis as $lok)
                            <option value="{{ $lok->id }}" {{ $order->lokasi_id == $lok->id ? 'selected' : '' }}>
                                {{ $lok->kode }} — {{ $lok->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Catatan lokasi</label>
                        <input type="text" name="catatan_lokasi" value="{{ $order->catatan_lokasi }}"
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                               placeholder="Baris ke-2, pojok kanan">
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 text-sm bg-brand text-white rounded-lg hover:bg-brand-hover transition-colors">
                        Simpan lokasi
                    </button>
                    <button type="button" @click="editLokasi = false"
                            class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tracking 7 tahap --}}
    @if(!$order->isSudahSelesai() || $order->status_berikut)
    <div class="bg-white border border-gray-100 rounded-xl p-6">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-5">Progress tracking</h2>

        @php
            $allStatuses = \App\Models\Order::STATUSES;
            $currentIdx  = array_search($order->status, $allStatuses);
            $labels = [
                'diterima'     => 'Diterima',
                'inspeksi'     => 'Inspeksi',
                'dicuci'       => 'Dicuci',
                'kering'       => 'Kering',
                'finishing'    => 'Finishing',
                'siap_diambil' => 'Siap Ambil',
                'selesai'      => 'Diambil',
            ];
        @endphp

        <div class="flex items-start gap-1 mb-5 overflow-x-auto pb-2">
            @foreach($allStatuses as $i => $s)
            @php
                $sIdx = $i;
                $done = $currentIdx !== false && $sIdx <= $currentIdx;
                $curr = $currentIdx !== false && $sIdx === $currentIdx;
            @endphp
            <div class="flex items-center gap-1 {{ !$loop->last ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center gap-1 shrink-0">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-medium shrink-0
                                {{ $curr ? 'bg-white text-gray-900 ring-2 ring-brand' : ($done ? 'bg-brand text-white' : 'bg-gray-100 text-gray-400') }}">
                        {{ ($done && !$curr) ? '✓' : ($i + 1) }}
                    </div>
                    <span class="text-xs text-center whitespace-nowrap {{ $done ? 'text-gray-700 font-medium' : 'text-gray-400' }}">
                        {{ $labels[$s] }}
                    </span>
                </div>
                @if(!$loop->last)
                <div class="flex-1 h-px mb-4 {{ $sIdx < $currentIdx ? 'bg-brand' : 'bg-gray-200' }}"></div>
                @endif
            </div>
            @endforeach
        </div>

        @if($order->status_berikut)
        <form method="POST" action="{{ route('orders.status', $order) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="{{ $order->status_berikut }}">
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="bg-brand text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-brand-hover transition-colors focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2">
                    Tandai: {{ $labels[$order->status_berikut] ?? ucfirst($order->status_berikut) }}
                    @if($order->status_berikut === 'selesai') — & Lunas @endif
                </button>
                @if($order->status_berikut === 'selesai' && $order->pembayaran?->status !== 'lunas')
                <p class="text-xs text-gray-400">Pembayaran otomatis ditandai lunas</p>
                @endif
            </div>
        </form>
        @endif
    </div>
    @endif

    {{-- Pembayaran & Profit --}}
    <div class="bg-white border border-gray-100 rounded-xl p-6">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Pembayaran & Profit</h2>

        <div class="grid grid-cols-2 gap-4 mb-4">
            @php
                $hargaBase    = $order->layanan->harga;
                $hargaEfektif = $order->harga_efektif;
                $adaHargaLokasi = $order->lokasi && $hargaEfektif !== $hargaBase;
            @endphp
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Harga layanan</span>
                    <span class="text-gray-700">Rp {{ number_format($hargaBase, 0, ',', '.') }}</span>
                </div>
                @if($adaHargaLokasi)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Harga di {{ $order->lokasi->nama }}</span>
                    <span class="font-medium text-gray-700">Rp {{ number_format($hargaEfektif, 0, ',', '.') }}/pasang</span>
                </div>
                @endif
                <div class="flex justify-between text-sm @if(!$adaHargaLokasi) border-t border-gray-100 pt-2 @endif">
                    <span class="text-gray-500">Jumlah pasang</span>
                    <span class="text-gray-700">× {{ $order->jumlah_pasang }}</span>
                </div>
                @if($order->diskon > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-green-600">Diskon voucher</span>
                    <span class="text-green-600">− Rp {{ number_format($order->diskon, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between text-sm border-t border-gray-100 pt-2">
                    <span class="font-semibold text-gray-900">Gross Sales</span>
                    <span class="font-semibold text-gray-900">Rp {{ number_format($order->gross_sales, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="font-semibold text-gray-900">Net Sales (dibayar)</span>
                    <span class="font-semibold text-gray-900">{{ $order->pembayaran?->total_format }}</span>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">HPP (COGS)</span>
                    <span class="text-red-600">(Rp {{ number_format($order->hpp, 0, ',', '.') }})</span>
                </div>
                <div class="flex justify-between text-sm border-t border-gray-100 pt-2">
                    <span class="font-semibold text-gray-900">Gross Profit</span>
                    <span class="font-semibold {{ $order->gross_profit >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        Rp {{ number_format($order->gross_profit, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Gross Margin</span>
                    <span class="font-semibold {{ $order->gross_margin >= 70 ? 'text-green-700' : ($order->gross_margin >= 40 ? 'text-amber-700' : 'text-red-700') }}">
                        {{ $order->gross_margin }}%
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-3 border-t border-gray-100">
            <span class="text-xs text-gray-500">
                Metode: <strong>{{ strtoupper($order->pembayaran?->metode ?? '—') }}</strong>
            </span>
            <span class="text-xs font-medium
                         {{ $order->pembayaran?->status === 'lunas' ? 'text-green-700' : 'text-amber-700' }}">
                {{ ucfirst($order->pembayaran?->status ?? '—') }}
            </span>
            @if($order->pembayaran?->dibayar_pada)
            <span class="text-xs text-gray-400 ml-auto">
                Dibayar {{ $order->pembayaran->dibayar_pada->isoFormat('D MMM Y') }}
            </span>
            @endif
            @if($order->pembayaran?->status !== 'lunas' && auth()->user()->hasPermission('orders.manage'))
            <form method="POST" action="{{ route('orders.lunas', $order) }}" class="ml-auto"
                  onsubmit="return confirm('Tandai pembayaran order {{ $order->no_order }} sebagai lunas?')">
                @csrf @method('PATCH')
                <button type="submit"
                        class="text-xs bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg transition-colors">
                    Tandai Lunas
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Review pelanggan --}}
    @if($order->review)
    <div class="bg-white border border-gray-100 rounded-xl p-6">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Ulasan pelanggan</h2>
        <div class="flex items-start gap-3">
            <span class="text-yellow-400 text-base tracking-tight">{{ str_repeat('★', $order->review->rating) }}{{ str_repeat('☆', 5 - $order->review->rating) }}</span>
            <div>
                @if($order->review->ulasan)
                <p class="text-sm text-gray-700">"{{ $order->review->ulasan }}"</p>
                @endif
                <p class="text-xs text-gray-400 mt-1">{{ $order->review->created_at->isoFormat('D MMM Y') }}</p>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

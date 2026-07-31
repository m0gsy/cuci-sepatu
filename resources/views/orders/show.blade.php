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
    <a href="{{ route('orders.invoice', $order) }}"
       class="text-xs bg-brand text-white px-4 py-2 rounded-lg hover:bg-brand-hover transition-colors">
        Download Invoice (PDF)
    </a>
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
                <p class="text-sm text-gray-500">{{ $order->no_hp_display }}</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $order->status_badge['class'] }}">
                {{ $order->status_badge['label'] }}
            </span>
        </div>

        <!-- Tabel Detail Item Sepatu -->
        <div class="mt-4 border-t border-gray-100 pt-4">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Item Sepatu</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs text-gray-400 font-medium">
                            <th class="py-2">Layanan</th>
                            <th class="py-2">Jenis</th>
                            <th class="py-2 text-center">Jumlah</th>
                            <th class="py-2">Detail (Merek, Warna, Kondisi)</th>
                            <th class="py-2 text-right">Harga</th>
                            <th class="py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-xs">
                        @forelse($order->items as $item)
                        <tr>
                            <td class="py-2.5 font-medium text-gray-900">{{ $item->layanan->nama ?? '—' }}</td>
                            <td class="py-2.5 text-gray-600">{{ $item->jenisBarang->nama ?? '—' }}</td>
                            <td class="py-2.5 text-center text-gray-700">{{ $item->jumlah_pasang }}</td>
                            <td class="py-2.5 text-gray-500">
                                {{ $item->merek ?? '—' }} ({{ $item->warna ?? '—' }})
                                @if($item->kondisi)
                                <br><span class="text-gray-400">Kondisi: {{ $item->kondisi }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 text-right text-gray-700">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                            <td class="py-2.5 text-right font-medium text-gray-900">Rp {{ number_format($item->harga_satuan * $item->jumlah_pasang, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        {{-- Fallback untuk order lama (sebelum sistem multi-item) --}}
                        @if($order->layanan_id || $order->jenis_sepatu || $order->jumlah_pasang)
                        <tr class="text-xs text-gray-500">
                            <td class="py-2.5 font-medium text-gray-900">{{ $order->layanan->nama ?? '—' }}</td>
                            <td class="py-2.5 text-gray-600">{{ $order->jenis_sepatu ?? '—' }}</td>
                            <td class="py-2.5 text-center text-gray-700">{{ $order->jumlah_pasang ?? 0 }}</td>
                            <td class="py-2.5 text-gray-500">
                                {{ $order->merek ?? '—' }} ({{ $order->warna ?? '—' }})
                                @if($order->kondisi)
                                <br><span class="text-gray-400">Kondisi: {{ $order->kondisi }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 text-right text-gray-700">Rp {{ number_format($order->harga_satuan ?? 0, 0, ',', '.') }}</td>
                            <td class="py-2.5 text-right font-medium text-gray-900">Rp {{ number_format(($order->harga_satuan ?? 0) * ($order->jumlah_pasang ?? 0), 0, ',', '.') }}</td>
                        </tr>
                        @else
                        <tr>
                            <td colspan="6" class="py-4 text-center text-gray-300">Belum ada item sepatu.</td>
                        </tr>
                        @endif
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-4 mt-4">
            <dl class="grid grid-cols-2 gap-x-8 gap-y-3">
                @php
                    $fields = [
                        'Total Pasang'  => $order->jumlah_pasang . ' pasang',
                        'Masuk'         => $order->created_at->isoFormat('D MMM Y, HH:mm'),
                        'Est. selesai'  => $order->estimasi_selesai?->isoFormat('D MMMM Y, HH:mm')
                                                  . ($order->terlambat ? ' ⚠' : ''),
                        'Diinput oleh'  => $order->user?->name ?? '—',
                    ];
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
            <div class="mt-4 pt-3 border-t border-gray-100 space-y-2" x-data="{ copied: false }">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Link tracking pelanggan</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ route('status.order', $order->token_publik) }}"
                           class="flex-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-mono focus:outline-none select-all text-gray-600">
                    <button type="button" 
                            @click="navigator.clipboard.writeText('{{ route('status.order', $order->token_publik) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="text-xs font-medium text-gray-750 bg-white border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-1 shrink-0">
                        <span x-text="copied ? 'Tersalin ✓' : 'Salin'"></span>
                    </button>
                    <a href="{{ route('status.order', $order->token_publik) }}" target="_blank"
                       class="text-xs font-medium text-white bg-brand px-3 py-1.5 rounded-lg hover:bg-brand-hover transition-colors flex items-center gap-1 shrink-0">
                        Buka ↗
                    </a>
                </div>
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

    {{-- Tracking 6 tahap --}}
    @if($order->status !== 'batal')
    <div class="bg-white border border-gray-100 rounded-xl p-6">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-5">Progress tracking</h2>

        @php
            // Hanya 5 step non-batal untuk ditampilkan di stepper
            $stepStatuses = ['draft', 'menunggu_pembayaran', 'diproses', 'siap_diambil', 'selesai'];
            $currentIdx   = array_search($order->status, $stepStatuses);
            $labels = [
                'draft'               => 'Draft',
                'menunggu_pembayaran' => 'Menunggu\nPembayaran',
                'diproses'            => 'Diproses',
                'siap_diambil'        => 'Siap Diambil',
                'selesai'             => 'Selesai',
            ];
        @endphp

        <div class="relative flex items-center w-full mb-6">
            <!-- Line container -->
            <div class="absolute left-[10%] right-[10%] top-3.5 h-0.5 bg-gray-100">
                @php
                    $percent = $currentIdx !== false ? ($currentIdx / (count($stepStatuses) - 1)) * 100 : 0;
                @endphp
                <div class="h-full bg-brand transition-all duration-500" style="width: {{ $percent }}%"></div>
            </div>

            @foreach($stepStatuses as $stepIdx => $s)
            @php
                $done = $currentIdx !== false && $stepIdx <= $currentIdx;
                $curr = $currentIdx !== false && $stepIdx === $currentIdx;
            @endphp
            <div class="flex-1 flex flex-col items-center relative z-10">
                <div class="bg-white px-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold
                                {{ $curr ? 'bg-white text-brand ring-2 ring-brand shadow-sm' : ($done ? 'bg-brand text-white' : 'bg-gray-100 text-gray-400') }}">
                        @if($done && !$curr)
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                        </svg>
                        @else
                        {{ $stepIdx + 1 }}
                        @endif
                    </div>
                </div>
                <span class="text-[10px] text-center mt-1.5 leading-tight font-semibold text-gray-400 {{ $done ? 'text-gray-700' : '' }} min-w-[65px] max-w-[85px] break-words">
                    {{ str_replace('\n', ' ', $labels[$s]) }}
                </span>
            </div>
            @endforeach
        </div>

        <div class="flex items-center gap-3">
            @if($order->status_berikut)
            <form method="POST" action="{{ route('orders.status', $order) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ $order->status_berikut }}">
                <button type="submit"
                        class="bg-brand text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-brand-hover transition-colors focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2">
                    Tandai: {{ $labels[$order->status_berikut] ?? ucfirst($order->status_berikut) }}
                </button>
            </form>
            @endif

            <!-- Button Cancel / Batal -->
            @if($order->canTransitionTo('batal'))
            <form method="POST" action="{{ route('orders.status', $order) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan order ini?')">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="batal">
                <button type="submit"
                        class="text-red-600 hover:text-red-700 text-sm font-medium px-4 py-2 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                    Batalkan Order
                </button>
            </form>
            @endif
        </div>
    </div>
    @endif

    {{-- Pembayaran & Profit --}}
    <div class="bg-white border border-gray-100 rounded-xl p-6">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Pembayaran & Profit</h2>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Total item</span>
                    <span class="text-gray-700">{{ $order->items->count() }} item ({{ $order->jumlah_pasang }} pasang)</span>
                </div>
                @if($order->diskon > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-green-600">Diskon voucher</span>
                    <span class="text-green-600">− Rp {{ number_format($order->diskon, 0, ',', '.') }}</span>
                </div>
                @endif
                @if(($order->diskon_poin ?? 0) > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-green-600">Diskon poin ({{ $order->poin_digunakan }} poin)</span>
                    <span class="text-green-600">− Rp {{ number_format($order->diskon_poin, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between text-sm border-t border-gray-100 pt-2">
                    <span class="font-semibold text-gray-900">Gross Sales</span>
                    <span class="font-semibold text-gray-900">Rp {{ number_format($order->gross_sales, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="font-semibold text-gray-900">Net Sales (dibayar)</span>
                    <span class="font-semibold text-gray-900">Rp {{ number_format($order->pembayaran?->total ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Total HPP</span>
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
            <span class="text-xs font-semibold px-2 py-0.5 rounded
                         {{ $order->pembayaran?->status === 'selesai' ? 'text-green-700 bg-green-50' : 'text-amber-700 bg-amber-50' }}">
                {{ $order->pembayaran?->status === 'selesai' ? 'Selesai' : 'Belum Selesai' }}
            </span>
            @if($order->pembayaran?->dibayar_pada)
            <span class="text-xs text-gray-400 ml-auto">
                Dibayar {{ $order->pembayaran->dibayar_pada->isoFormat('D MMM Y') }}
            </span>
            @endif
            @if($order->pembayaran?->status !== 'selesai' && auth()->user()->hasPermission('orders.manage'))
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

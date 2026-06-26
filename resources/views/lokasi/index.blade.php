@extends('layouts.app')
@section('title', 'Mapping Lokasi Sepatu')

@section('header-actions')
<button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
        class="bg-brand text-white text-xs font-medium px-4 py-2 rounded-lg hover:bg-brand-hover transition-colors">
    + Tambah lokasi
</button>
@endsection

@section('content')
<div class="space-y-5">

{{-- Peta visual --}}
<div>
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Status lokasi saat ini</p>
    <div class="grid grid-cols-4 gap-3">
        @foreach($lokasis as $lok)
        <div class="bg-white border border-gray-100 rounded-xl p-4">
            <div class="flex items-start justify-between mb-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-bold bg-gray-100 text-gray-700">
                    {{ $lok->kode }}
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $lok->status_badge }}">
                    {{ $lok->order_aktif === 0 ? 'Kosong' : ($lok->order_aktif <= 3 ? 'Terisi' : 'Penuh') }}
                </span>
            </div>
            <p class="text-sm font-medium text-gray-900 mb-1">{{ $lok->nama }}</p>
            @if($lok->harga_custom)
            <p class="text-xs font-medium {{ $lok->harga_tambahan > 0 ? 'text-amber-700' : 'text-green-700' }} mb-1">
                {{ $lok->harga_tambahan_format }}
            </p>
            @else
            <p class="text-xs text-gray-400 mb-1">Harga standar</p>
            @endif
            <div class="flex items-center gap-3 text-xs">
                <span class="text-amber-700 font-medium">{{ $lok->order_aktif }} aktif</span>
                <span class="text-green-700 font-medium">{{ $lok->order_selesai }} selesai</span>
            </div>
        </div>
        @endforeach
        @if($lokasis->isEmpty())
        <div class="col-span-4 bg-white border border-gray-100 rounded-xl p-10 text-center">
            <p class="text-sm text-gray-400">Belum ada lokasi. Tambahkan lokasi pertama.</p>
        </div>
        @endif
    </div>
</div>

{{-- Order aktif per lokasi --}}
@if($orderAktif->count() > 0)
<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <p class="text-sm font-semibold text-gray-900">Sepatu yang sedang di toko</p>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full min-w-[640px]">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 whitespace-nowrap">No. order</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Pelanggan</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Layanan</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Lokasi</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 whitespace-nowrap">Total</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Status</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 whitespace-nowrap">Estimasi</th>
            </tr>
        </thead>
        <tbody>
        @foreach($orderAktif as $o)
            <tr class="border-b border-gray-50 hover:bg-gray-50">
                <td class="px-5 py-3 whitespace-nowrap">
                    <a href="{{ route('orders.show', $o) }}"
                       class="text-xs font-mono text-gray-500 hover:text-gray-900">{{ $o->no_order }}</a>
                </td>
                <td class="px-5 py-3 text-sm text-gray-800">{{ $o->nama_pelanggan }}</td>
                <td class="px-5 py-3 text-xs text-gray-500">{{ $o->layanan->nama }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs font-mono font-bold bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded">
                        {{ $o->lokasi->kode }}
                    </span>
                    <span class="text-xs text-gray-500 ml-1">{{ $o->lokasi->nama }}</span>
                    @if($o->catatan_lokasi)
                    <p class="text-xs text-gray-400">{{ $o->catatan_lokasi }}</p>
                    @endif
                </td>
                <td class="px-5 py-3 text-sm font-medium text-right text-gray-900 whitespace-nowrap">
                    {{ $o->pembayaran?->total_format ?? '—' }}
                </td>
                <td class="px-5 py-3 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $o->status_badge['class'] }}">
                        {{ $o->status_badge['label'] }}
                    </span>
                </td>
                <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">
                    {{ $o->estimasi_selesai->format('d M Y') }}
                    @if($o->terlambat)<span class="text-red-500 ml-1">⚠</span>@endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- Daftar & kelola lokasi --}}
<div class="space-y-3">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-900">Daftar lokasi</p>
            <p class="text-xs text-gray-400 mt-0.5">Atur harga per layanan untuk setiap lokasi secara independen</p>
        </div>
    </div>

    @forelse($lokasis as $lok)
    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden"
         x-data="{ edit: false, hargaOpen: false }">

        {{-- Header lokasi --}}
        <div class="px-5 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="font-mono font-bold text-sm bg-gray-100 text-gray-700 px-2.5 py-1 rounded-lg">
                    {{ $lok->kode }}
                </span>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $lok->nama }}</p>
                    @if($lok->deskripsi)
                    <p class="text-xs text-gray-400">{{ $lok->deskripsi }}</p>
                    @endif
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                             {{ $lok->aktif ? 'bg-green-50 text-green-700 ring-1 ring-green-200' : 'bg-gray-100 text-gray-500' }}">
                    {{ $lok->aktif ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-500">
                    <strong>{{ $lok->order_aktif }}</strong> aktif
                </span>
                <button @click="hargaOpen = !hargaOpen"
                        class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1.5 rounded-lg hover:bg-blue-100 font-medium">
                    Atur harga layanan
                </button>
                <button @click="edit = !edit"
                        class="text-xs text-gray-400 hover:text-gray-700 border border-gray-200 px-3 py-1.5 rounded-lg">
                    Edit lokasi
                </button>
                <form method="POST" action="{{ route('lokasi.toggle', $lok) }}" class="inline">
                    @csrf @method('PATCH')
                    <button type="submit"
                            class="text-xs border px-3 py-1.5 rounded-lg {{ $lok->aktif ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-green-200 text-green-700 hover:bg-green-50' }}">
                        {{ $lok->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Tabel harga per layanan --}}
        <div x-show="hargaOpen" class="border-t border-gray-100">
            <div class="px-5 py-3 bg-blue-50 border-b border-blue-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-blue-800">Harga per Layanan — {{ $lok->nama }}</p>
                    <p class="text-xs text-blue-600 mt-0.5">
                        Harga yang diset di sini menggantikan harga standar layanan untuk lokasi ini.
                        Kosong = pakai harga standar.
                    </p>
                </div>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 w-1/3">Layanan</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Harga standar</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Harga di {{ $lok->nama }}</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 w-40">Set harga</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($layanans as $layan)
                    @php
                        $override = $lok->layanans->firstWhere('id', $layan->id);
                        $hargaOverride = $override?->pivot->harga;
                    @endphp
                    <tr class="hover:bg-gray-50" x-data="{ inputHarga: {{ $hargaOverride ?? $layan->harga }} }">
                        <td class="px-5 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $layan->nama }}</p>
                        </td>
                        <td class="px-5 py-3 text-right text-sm text-gray-500">
                            Rp {{ number_format($layan->harga, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-right">
                            @if($hargaOverride !== null)
                            <span class="text-sm font-semibold {{ $hargaOverride < $layan->harga ? 'text-green-700' : ($hargaOverride > $layan->harga ? 'text-amber-700' : 'text-gray-900') }}">
                                Rp {{ number_format($hargaOverride, 0, ',', '.') }}
                            </span>
                            @if($hargaOverride !== $layan->harga)
                            <span class="text-xs ml-1 {{ $hargaOverride < $layan->harga ? 'text-green-600' : 'text-amber-600' }}">
                                ({{ $hargaOverride < $layan->harga ? '-' : '+' }}Rp {{ number_format(abs($hargaOverride - $layan->harga), 0, ',', '.') }})
                            </span>
                            @endif
                            @else
                            <span class="text-xs text-gray-400">Standar</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-1.5">
                                <form method="POST" action="{{ route('lokasi.harga-layanan.set', $lok) }}"
                                      class="flex items-center gap-1.5">
                                    @csrf
                                    <input type="hidden" name="layanan_id" value="{{ $layan->id }}">
                                    <input type="number" name="harga" x-model="inputHarga" min="0"
                                           class="w-28 rounded-lg border border-gray-200 px-2 py-1 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    <button type="submit"
                                            class="text-xs bg-blue-600 text-white px-2.5 py-1 rounded-lg hover:bg-blue-700">
                                        Set
                                    </button>
                                </form>
                                @if($hargaOverride !== null)
                                <form method="POST"
                                      action="{{ route('lokasi.harga-layanan.hapus', [$lok, $layan]) }}"
                                      onsubmit="return confirm('Reset ke harga standar?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="text-xs text-gray-400 hover:text-red-600 px-1.5 py-1 rounded border border-gray-200 hover:border-red-200">
                                        Reset
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @if($layanans->isEmpty())
                    <tr>
                        <td colspan="4" class="px-5 py-6 text-center text-xs text-gray-400">
                            Belum ada layanan aktif. Tambahkan layanan terlebih dahulu.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Form edit lokasi --}}
        <div x-show="edit" class="border-t border-gray-100 px-5 py-4 bg-gray-50">
            <form method="POST" action="{{ route('lokasi.update', $lok) }}">
                @csrf @method('PUT')
                <div class="grid grid-cols-3 gap-3 mb-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Kode</label>
                        <input type="text" name="kode" value="{{ $lok->kode }}"
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                               required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nama</label>
                        <input type="text" name="nama" value="{{ $lok->nama }}"
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                               required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Deskripsi</label>
                        <input type="text" name="deskripsi" value="{{ $lok->deskripsi }}"
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                               placeholder="Opsional">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit"
                            class="px-4 py-2 text-sm bg-brand text-white rounded-lg hover:bg-brand-hover">
                        Simpan
                    </button>
                    <button type="button" @click="edit = false"
                            class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
    @empty
    <div class="bg-white border border-gray-100 rounded-xl p-10 text-center">
        <p class="text-sm text-gray-400">Belum ada lokasi. Tambahkan lokasi pertama.</p>
    </div>
    @endforelse
</div>

</div>

{{-- Modal tambah --}}
<div id="modal-tambah" class="hidden fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-gray-100 p-6 w-full max-w-md" x-data="{ custom: false }">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-sm font-semibold text-gray-900">Tambah lokasi baru</h2>
            <button onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-700 text-lg leading-none">✕</button>
        </div>
        <form method="POST" action="{{ route('lokasi.store') }}">
            @csrf
            <div class="space-y-3 mb-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Kode lokasi</label>
                    <input type="text" name="kode"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                           placeholder="RAK-A1 / LANTAI-2 / VIP" required>
                    <p class="text-xs text-gray-400 mt-1">Kode singkat yang tampil di daftar order</p>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Nama lokasi</label>
                    <input type="text" name="nama"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                           placeholder="Rak A Baris 1" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Deskripsi (opsional)</label>
                    <input type="text" name="deskripsi"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div class="border border-gray-100 rounded-xl p-3">
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer mb-1">
                        <input type="checkbox" name="harga_custom" value="1"
                               @change="custom = $event.target.checked"
                               class="rounded border-gray-300">
                        Harga khusus untuk lokasi ini
                    </label>
                    <p class="text-xs text-gray-400 mb-3">Aktifkan jika lokasi ini memiliki biaya tambahan atau diskon</p>
                    <div x-show="custom" class="space-y-2">
                        <label class="block text-xs text-gray-500">Tambahan harga (Rp)</label>
                        <input type="number" name="harga_tambahan" value="0"
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                               placeholder="Misal: 5000 (surcharge) atau -5000 (diskon)">
                        <p class="text-xs text-gray-400">Angka positif = biaya tambah, negatif = diskon</p>
                    </div>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                        class="flex-1 py-2.5 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
                <button type="submit"
                        class="flex-1 py-2.5 text-sm bg-brand text-white rounded-lg hover:bg-brand-hover">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection

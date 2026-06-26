@extends('layouts.app')
@section('title', 'Voucher')

@section('header-actions')
    <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
            class="text-xs bg-brand text-white px-4 py-2 rounded-lg hover:bg-brand-hover">
        + Tambah voucher
    </button>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Tabel voucher --}}
    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe / Nilai</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min. Transaksi</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kuota</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expired</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($vouchers as $voucher)
                <tr class="hover:bg-gray-50 transition-colors" x-data="{ editOpen: false }">
                    <td class="px-5 py-3.5">
                        <span class="font-mono font-semibold text-gray-900">{{ $voucher->kode }}</span>
                        @if($voucher->deskripsi)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $voucher->deskripsi }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="font-semibold text-gray-900">{{ $voucher->nilai_format }}</span>
                        <span class="text-xs text-gray-400 ml-1">{{ $voucher->tipe === 'persen' ? 'diskon' : 'potongan' }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-gray-600">
                        @if($voucher->min_transaksi > 0)
                            Rp {{ number_format($voucher->min_transaksi, 0, ',', '.') }}
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-gray-600">
                        @if($voucher->kuota !== null)
                            {{ $voucher->terpakai }} / {{ $voucher->kuota }}
                        @else
                            <span class="text-gray-400">Tidak terbatas</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-gray-600">
                        @if($voucher->expired_at)
                            {{ $voucher->expired_at->isoFormat('D MMM Y') }}
                            @if($voucher->expired_at->isPast())
                                <span class="text-red-500 text-xs ml-1">(expired)</span>
                            @endif
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $voucher->status_badge }}">
                            {{ $voucher->status_label }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2 justify-end">
                            <button @click="editOpen = !editOpen"
                                    class="text-xs text-gray-400 hover:text-gray-700 border border-gray-200 px-2.5 py-1 rounded-lg">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('vouchers.toggle', $voucher) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="text-xs {{ $voucher->aktif ? 'text-amber-600 border-amber-200' : 'text-green-600 border-green-200' }} border px-2.5 py-1 rounded-lg hover:bg-gray-50">
                                    {{ $voucher->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            @if(!$voucher->orders_count)
                            <form method="POST" action="{{ route('vouchers.destroy', $voucher) }}"
                                  onsubmit="return confirm('Hapus voucher {{ $voucher->kode }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                            </form>
                            @endif
                        </div>

                        {{-- Form edit inline --}}
                        <div x-show="editOpen" class="mt-3 pt-3 border-t border-gray-100">
                            <form method="POST" action="{{ route('vouchers.update', $voucher) }}">
                                @csrf @method('PUT')
                                <div class="grid grid-cols-3 gap-2 mb-2">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Tipe</label>
                                        <select name="tipe" class="w-full rounded border border-gray-200 px-2 py-1.5 text-xs">
                                            <option value="persen" {{ $voucher->tipe === 'persen' ? 'selected' : '' }}>Persen (%)</option>
                                            <option value="nominal" {{ $voucher->tipe === 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Nilai</label>
                                        <input type="number" name="nilai" value="{{ $voucher->nilai }}" min="1"
                                               class="w-full rounded border border-gray-200 px-2 py-1.5 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Min. Transaksi</label>
                                        <input type="number" name="min_transaksi" value="{{ $voucher->min_transaksi }}" min="0"
                                               class="w-full rounded border border-gray-200 px-2 py-1.5 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Kuota</label>
                                        <input type="number" name="kuota" value="{{ $voucher->kuota }}" min="1"
                                               placeholder="Tak terbatas"
                                               class="w-full rounded border border-gray-200 px-2 py-1.5 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Expired</label>
                                        <input type="date" name="expired_at"
                                               value="{{ $voucher->expired_at?->format('Y-m-d') }}"
                                               class="w-full rounded border border-gray-200 px-2 py-1.5 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Deskripsi</label>
                                        <input type="text" name="deskripsi" value="{{ $voucher->deskripsi }}"
                                               class="w-full rounded border border-gray-200 px-2 py-1.5 text-xs">
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs bg-brand text-white rounded-lg hover:bg-brand-hover">
                                        Simpan
                                    </button>
                                    <button type="button" @click="editOpen = false"
                                            class="px-3 py-1.5 text-xs text-gray-500 border border-gray-200 rounded-lg">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400">
                        Belum ada voucher. Tambahkan voucher pertama Anda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- Modal tambah voucher --}}
<div id="modal-tambah" class="hidden fixed inset-0 bg-black/30 flex items-center justify-center z-50" x-data>
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-sm font-semibold text-gray-900">Tambah Voucher Baru</h2>
            <button onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-700 text-lg leading-none">✕</button>
        </div>
        <form method="POST" action="{{ route('vouchers.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="col-span-2">
                    <label class="block text-xs text-gray-500 mb-1.5">Kode voucher <span class="text-red-400">*</span></label>
                    <input type="text" name="kode" placeholder="HEMAT10" style="text-transform:uppercase"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand font-mono"
                           required>
                    @error('kode')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Tipe diskon <span class="text-red-400">*</span></label>
                    <select name="tipe" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                        <option value="persen">Persen (%)</option>
                        <option value="nominal">Nominal (Rp)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Nilai <span class="text-red-400">*</span></label>
                    <input type="number" name="nilai" min="1" placeholder="10"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                           required>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Minimum transaksi</label>
                    <input type="number" name="min_transaksi" min="0" placeholder="0"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Kuota pemakaian</label>
                    <input type="number" name="kuota" min="1" placeholder="Tak terbatas"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs text-gray-500 mb-1.5">Expired (opsional)</label>
                    <input type="date" name="expired_at"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs text-gray-500 mb-1.5">Deskripsi</label>
                    <input type="text" name="deskripsi" placeholder="Voucher promo Lebaran..."
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button"
                        onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2 text-sm bg-brand text-white rounded-lg hover:bg-brand-hover">
                    Simpan voucher
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

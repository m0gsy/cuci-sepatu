@extends('layouts.app')
@section('title', 'Edit Order ' . $order->no_order)

@section('header-actions')
    <a href="{{ route('orders.show', $order) }}" class="text-xs text-gray-400 hover:text-gray-700">← Kembali</a>
@endsection

@section('content')
<div class="max-w-4xl">
<form method="POST" action="{{ route('orders.update', $order) }}" x-data="editForm()">
@csrf @method('PUT')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Kiri: Pelanggan & Items -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Data Pelanggan -->
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-sm">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Data pelanggan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Nama pelanggan <span class="text-red-400">*</span></label>
                    <input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan', $order->nama_pelanggan) }}"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                    @error('nama_pelanggan')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">No. HP <span class="text-red-400">*</span></label>
                    <input type="text" name="no_hp" value="{{ old('no_hp', $order->no_hp_display) }}"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                </div>
            </div>
        </div>

        <!-- Lokasi Penyimpanan -->
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-sm">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Lokasi Penyimpanan</h2>
            @if($lokasis->count() > 0)
            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1.5">Lokasi penyimpanan sepatu</label>
                <select name="lokasi_id"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                        @change="pilihLokasi($event.target.value)">
                    <option value="">-- Pilih lokasi (opsional) --</option>
                    @foreach($lokasis as $lok)
                    <option value="{{ $lok->id }}" {{ old('lokasi_id', $order->lokasi_id) == $lok->id ? 'selected' : '' }}>
                        {{ $lok->kode }} — {{ $lok->nama }}
                        @if($lok->harga_custom)
                            ({{ $lok->harga_tambahan_format }})
                        @endif
                    </option>
                    @endforeach
                </select>
                <!-- Info harga lokasi -->
                <p class="text-xs mt-1.5" x-show="lokasiLabel !== ''"
                   :class="hargaTambahan > 0 ? 'text-amber-700' : (hargaTambahan < 0 ? 'text-green-700' : 'text-gray-400')"
                   x-text="'Harga lokasi ini: ' + lokasiLabel">
                </p>
            </div>
            <div x-show="lokasi_id" class="mb-4">
                <label class="block text-xs text-gray-500 mb-1.5">Catatan lokasi</label>
                <input type="text" name="catatan_lokasi" value="{{ old('catatan_lokasi', $order->catatan_lokasi) }}"
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                       placeholder="Misal: baris ke-2 dari kiri">
            </div>
            @endif
        </div>

        <!-- Items Detail Order -->
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Item Sepatu</h2>
                <button type="button" @click="addItem()" class="text-xs text-brand font-medium hover:text-brand-hover">+ Tambah Sepatu</button>
            </div>

            <div class="space-y-4">
                <template x-for="(item, index) in items" :key="index">
                    <div class="border border-gray-100 rounded-xl p-4 space-y-3 bg-gray-50/50 relative">
                        <button type="button" x-show="items.length > 1" @click="removeItem(index)"
                                class="absolute top-3 right-3 text-gray-400 hover:text-red-500 text-sm">✕</button>

                        <p class="text-xs font-semibold text-gray-700" x-text="'Sepatu #' + (index + 1)"></p>

                        <!-- Hidden ID for tracking existing items -->
                        <input type="hidden" :name="'items[' + index + '][id]'" :value="item.id">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Layanan <span class="text-red-400">*</span></label>
                                <select :name="'items[' + index + '][layanan_id]'"
                                        x-model="item.layanan_id"
                                        @change="hitungHargaItem(item)"
                                        class="w-full rounded-lg border border-gray-200 px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand" required>
                                    <option value="">-- Pilih layanan --</option>
                                    @foreach($layanans as $l)
                                    <option value="{{ $l->id }}">{{ $l->nama }} (Rp {{ number_format($l->harga, 0, ',', '.') }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Jenis <span class="text-red-400">*</span></label>
                                <select :name="'items[' + index + '][jenis_barang_id]'"
                                        x-model="item.jenis_barang_id"
                                        class="w-full rounded-lg border border-gray-200 px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand" required>
                                    <option value="">-- Pilih jenis --</option>
                                    @foreach($jenisBarangs as $jb)
                                    <option value="{{ $jb->id }}">{{ $jb->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Jumlah pasang <span class="text-red-400">*</span></label>
                                <input type="number" :name="'items[' + index + '][jumlah_pasang]'"
                                       x-model.number="item.jumlah_pasang"
                                       @input="hitungHargaItem(item)"
                                       min="1" max="20"
                                       class="w-full rounded-lg border border-gray-200 px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Merek</label>
                                <input type="text" :name="'items[' + index + '][merek]'" x-model="item.merek" placeholder="Nike, Adidas..."
                                       class="w-full rounded-lg border border-gray-200 px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Warna</label>
                                <input type="text" :name="'items[' + index + '][warna]'" x-model="item.warna" placeholder="Hitam, Putih..."
                                       class="w-full rounded-lg border border-gray-200 px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Kondisi awal</label>
                                <input type="text" :name="'items[' + index + '][kondisi]'" x-model="item.kondisi" placeholder="Kotor parah, sol retak..."
                                       class="w-full rounded-lg border border-gray-200 px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand">
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs pt-1 border-t border-gray-100/50">
                            <span class="text-gray-400" x-show="item.hargaSatuan > 0" x-text="'Harga: ' + formatRupiah(item.hargaSatuan) + ' / pasang'"></span>
                            <span class="font-semibold text-gray-700" x-show="item.hargaSatuan > 0" x-text="'Subtotal: ' + formatRupiah(item.hargaSatuan * item.jumlah_pasang)"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Catatan Tambahan -->
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-sm">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Catatan tambahan</h2>
            <textarea name="catatan" rows="3"
                      class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none"
                      placeholder="Masukkan catatan order (opsional)...">{{ $order->catatan }}</textarea>
        </div>
    </div>

    <!-- Form Kanan: Ringkasan & Submit -->
    <div class="space-y-6">
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-sm space-y-5 sticky top-5">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Ringkasan transaksi</h2>

            <div class="space-y-4 text-sm">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Estimasi selesai <span class="text-red-400">*</span></label>
                    <input type="datetime-local" name="estimasi_selesai"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                           value="{{ $order->estimasi_selesai?->format('Y-m-d\TH:i') }}" required>
                </div>
            </div>

            <!-- Breakdown total -->
            <div class="border-t border-gray-100 pt-4 space-y-2 text-xs">
                <div class="flex justify-between text-gray-500">
                    <span>Jumlah pasang</span>
                    <span x-text="totalJumlah + ' pasang'"></span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>Subtotal</span>
                    <span x-text="formatRupiah(subtotal)"></span>
                </div>
                <div class="flex justify-between text-green-600 font-medium" x-show="voucherDiskon > 0">
                    <span>Diskon voucher</span>
                    <span x-text="'-' + formatRupiah(voucherDiskon)"></span>
                </div>
                <div class="flex justify-between font-semibold text-sm text-gray-900 border-t border-gray-100 pt-2">
                    <span>Grand Total</span>
                    <span x-text="formatRupiah(grandTotal)"></span>
                </div>
            </div>

            <!-- Profit Preview -->
            <div class="bg-gray-50 rounded-lg p-3 text-xs space-y-1.5" x-show="hppTotal > 0">
                <div class="flex justify-between text-gray-500">
                    <span>Estimasi HPP</span>
                    <span x-text="formatRupiah(hppTotal)"></span>
                </div>
                <div class="flex justify-between text-green-700 font-semibold">
                    <span>Gross Profit</span>
                    <span x-text="formatRupiah(grandTotal - hppTotal)"></span>
                </div>
                <div class="flex justify-between text-gray-400">
                    <span>Profit Margin</span>
                    <span x-text="(grandTotal > 0 ? Math.round(((grandTotal - hppTotal) / grandTotal) * 100) : 0) + '%'"></span>
                </div>
            </div>

            <div class="flex gap-2 pt-2">
                <a href="{{ route('orders.show', $order) }}" class="flex-1 py-2 text-center text-xs text-gray-500 hover:text-gray-700 border border-gray-100 rounded-lg">Batal</a>
                <button type="submit" class="flex-1 py-2 text-xs bg-brand text-white font-medium rounded-lg hover:bg-brand-hover">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

</form>
</div>

<script>
function editForm() {
    // Standardize existing items
    const rawItems = @json(old('items', $order->items->toArray()));
    const mappedItems = rawItems.map(item => ({
        id: item.id,
        layanan_id: item.layanan_id,
        jenis_barang_id: item.jenis_barang_id,
        jumlah_pasang: item.jumlah_pasang,
        merek: item.merek || '',
        warna: item.warna || '',
        kondisi: item.kondisi || '',
        hargaSatuan: item.harga_satuan || 0,
        hargaLayananAsli: 0,
        hppSatuan: 0,
    }));

    return {
        items: mappedItems,
        layananMap: {
            @foreach($layanans as $l)
                "{{ $l->id }}": {
                    harga: {{ $l->harga }},
                    hpp: {{ $l->total_hpp }},
                    menit: {{ match(strtolower($l->estimasi_satuan)) {
                        'jam' => $l->estimasi_nilai * 60,
                        'minggu' => $l->estimasi_nilai * 10080,
                        default => $l->estimasi_nilai * 1440,
                    } }}
                },
            @endforeach
        },
        lokasi_id: "{{ old('lokasi_id', $order->lokasi_id) }}",
        hargaTambahan: 0,
        hargaCustom: false,
        lokasiLabel: '',
        hargaPerLayanan: {},
        voucherDiskon: {{ $order->diskon ?? 0 }},

        init() {
            this.pilihLokasi(this.lokasi_id);
        },

        addItem() {
            this.items.push({
                layanan_id: '',
                jenis_barang_id: '',
                jumlah_pasang: 1,
                merek: '',
                warna: '',
                kondisi: '',
                hargaSatuan: 0,
                hargaLayananAsli: 0,
                hppSatuan: 0,
            });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },

        async pilihLokasi(val) {
            this.lokasi_id = val;
            this.hargaPerLayanan = {};
            this.hargaTambahan = 0;
            this.hargaCustom = false;
            this.lokasiLabel = '';

            if (val) {
                try {
                    const res = await fetch(`/lokasi/${val}/harga`);
                    const data = await res.json();
                    this.hargaPerLayanan = data.harga_per_layanan || {};
                    this.hargaTambahan = data.harga_tambahan || 0;
                    this.hargaCustom = data.harga_custom || false;
                    this.lokasiLabel = data.label || '';
                } catch (e) {
                    console.error("Gagal memuat harga lokasi: ", e);
                }
            }

            // Hitung ulang semua item
            this.items.forEach(item => this.hitungHargaItem(item));
        },

        hitungHargaItem(item) {
            if (!item.layanan_id) {
                item.hargaSatuan = 0;
                item.hargaLayananAsli = 0;
                item.hppSatuan = 0;
                return;
            }

            const lData = this.layananMap[item.layanan_id];
            if (lData) {
                item.hargaLayananAsli = lData.harga;
                item.hppSatuan = lData.hpp;

                if (this.lokasi_id) {
                    if (this.hargaPerLayanan[item.layanan_id] !== undefined) {
                        item.hargaSatuan = this.hargaPerLayanan[item.layanan_id];
                    } else if (this.hargaCustom) {
                        item.hargaSatuan = lData.harga + this.hargaTambahan;
                    } else {
                        item.hargaSatuan = lData.harga;
                    }
                } else {
                    item.hargaSatuan = lData.harga;
                }
            }
        },

        get totalJumlah() {
            return this.items.reduce((sum, item) => sum + (parseInt(item.jumlah_pasang) || 0), 0);
        },

        get subtotal() {
            return this.items.reduce((sum, item) => sum + ((parseInt(item.jumlah_pasang) || 0) * (item.hargaSatuan || 0)), 0);
        },

        get hppTotal() {
            return this.items.reduce((sum, item) => sum + ((parseInt(item.jumlah_pasang) || 0) * (item.hppSatuan || 0)), 0);
        },

        get grandTotal() {
            return Math.max(0, this.subtotal - this.voucherDiskon);
        },

        formatRupiah(n) {
            if (!n && n !== 0) return 'Rp 0';
            const abs = Math.abs(Math.round(n));
            const formatted = 'Rp ' + abs.toLocaleString('id-ID');
            return n < 0 ? '-' + formatted : formatted;
        }
    };
}
</script>
@endsection

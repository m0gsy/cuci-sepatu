@extends('layouts.app')
@section('title', 'Order baru')

@section('content')
<div class="max-w-4xl">
<form method="POST" action="{{ route('orders.store') }}" x-data="orderForm()" @submit="submitForm($event)">
@csrf
<input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}">

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Kiri: Pelanggan & Items -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Data Pelanggan -->
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-sm">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Data pelanggan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div x-data="pelangganAuto(@js(old('nama_pelanggan', '')))" class="relative" @set-nama-pelanggan.window="nama = $event.detail">
                    <label class="block text-xs text-gray-500 mb-1.5">Nama pelanggan <span class="text-red-400">*</span></label>
                    <input type="text" name="nama_pelanggan"
                           x-model="nama"
                           @input="cari($event.target.value)"
                           @keydown.escape="tutup()"
                           @keydown.arrow-down.prevent="fokusItem(1)"
                           @keydown.arrow-up.prevent="fokusItem(-1)"
                           @keydown.enter.prevent="pilihFokus()"
                           @blur="setTimeout(() => tutup(), 150)"
                           autocomplete="off"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                           placeholder="Budi Santoso" required>
                    <!-- Dropdown saran -->
                    <div x-show="terbuka" x-cloak
                         class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                        <template x-for="(p, i) in saran" :key="p.no_hp">
                            <button type="button"
                                    @mousedown.prevent="pilih(p)"
                                    :class="i === fokus ? 'bg-brand text-white' : 'hover:bg-gray-50'"
                                    class="w-full text-left px-3 py-2 text-sm flex items-center justify-between gap-2">
                                <span x-text="p.nama" class="font-medium truncate"></span>
                                <span x-text="p.no_hp"
                                      :class="i === fokus ? 'text-white/70' : 'text-gray-400'"
                                      class="text-xs shrink-0"></span>
                            </button>
                        </template>
                    </div>
                </div>
                <div x-data="phoneInput(@js(old('no_hp', '')))" @set-no-hp.window="setNoHp($event.detail)">
                    <label class="block text-xs text-gray-500 mb-1.5">No. HP <span class="text-red-400">*</span></label>
                    <input type="text" name="no_hp"
                           x-model="noHp"
                           @input.debounce.500ms="cariPoin()"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                           placeholder="0812-xxxx-xxxx" required>
                    <!-- Duplicate suggestion banner -->
                    <div x-show="pelangganDitemukan" x-cloak
                         class="mt-2 p-2.5 bg-amber-50 border border-amber-200 rounded-lg flex items-center justify-between gap-2">
                        <div class="text-xs text-amber-800">
                            <span class="font-semibold">Pelanggan ditemukan:</span>
                            <span x-text="namaDitemukan"></span>
                            <span class="text-amber-600" x-show="poinDitemukan > 0" x-text="'(' + poinDitemukan + ' poin)'"></span>
                        </div>
                        <button type="button" @click="pakaiPelanggan()"
                                class="text-xs px-2 py-1 bg-amber-100 hover:bg-amber-200 text-amber-900 rounded font-medium whitespace-nowrap">
                            Pakai ini
                        </button>
                    </div>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Pelanggan otomatis terdaftar berdasarkan nomor HP.</p>
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
                    <option value="{{ $lok->id }}" @selected((string) old('lokasi_id') === (string) $lok->id)>
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
                <input type="text" name="catatan_lokasi"
                       value="{{ old('catatan_lokasi') }}"
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
                      placeholder="Masukkan catatan order (opsional)...">{{ old('catatan') }}</textarea>
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
                           :value="@js(old('estimasi_selesai')) || estimasiTanggal" required>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Metode bayar <span class="text-red-400">*</span></label>
                    <select name="metode_bayar"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                        <option value="tempo" @selected(old('metode_bayar', 'tempo') === 'tempo')>Bayar saat ambil</option>
                        <option value="cash" @selected(old('metode_bayar') === 'cash')>Cash (langsung)</option>
                        <option value="qris" @selected(old('metode_bayar') === 'qris')>QRIS</option>
                        <option value="transfer" @selected(old('metode_bayar') === 'transfer')>Transfer (DP)</option>
                        <option value="lunas" @selected(old('metode_bayar') === 'lunas')>Sudah lunas</option>
                    </select>
                </div>

                <!-- Voucher -->
                <div x-data="voucherForm(@js(old('voucher_kode', '')))">
                    <label class="block text-xs text-gray-500 mb-1">Kode voucher (opsional)</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="kode" @input="reset()" placeholder="DISKON10"
                               style="text-transform:uppercase"
                               class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand">
                        <button type="button" @click="cek(subtotal)"
                                class="px-3 py-2 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700">
                            Cek
                        </button>
                    </div>
                    <input type="hidden" name="voucher_kode" :value="valid ? kode : ''">
                    <p x-show="pesan" :class="valid ? 'text-green-700' : 'text-red-600'" class="text-xs mt-1.5 font-medium" x-text="pesan"></p>
                </div>

                <!-- Poin Redemption -->
                <div x-data="poinForm()" @poin-loaded.window="muat($event.detail)" class="border border-gray-100 rounded-xl p-3 bg-gray-50/50 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">Tukar Poin Reward</span>
                        <span class="text-xs text-gray-400" x-show="poinTersedia > 0" x-text="poinTersedia + ' poin tersedia (Rp ' + nilaiRupiah.toLocaleString('id-ID') + ')'"></span>
                        <span class="text-xs text-gray-300" x-show="poinTersedia === 0">Belum ada poin</span>
                    </div>
                    <template x-if="poinTersedia > 0">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="tukar_poin" value="1"
                                   x-model="aktif"
                                   @change="$dispatch('poin-toggled', { diskon: aktif ? Math.min(nilaiRupiah, Math.max(0, subtotal - voucherDiskon)) : 0 })"
                                   class="rounded border-gray-300 text-brand focus:ring-brand">
                            <span class="text-xs text-gray-700">Gunakan semua poin sebagai diskon</span>
                            <span class="text-xs font-semibold text-green-700" x-show="aktif"
                                  x-text="'-Rp ' + Math.min(nilaiRupiah, Math.max(0, subtotal - voucherDiskon)).toLocaleString('id-ID')"></span>
                        </label>
                    </template>
                    <template x-if="poinTersedia === 0 && !loaded">
                        <p class="text-xs text-gray-400">Masukkan nomor HP untuk cek poin.</p>
                    </template>
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
                <div class="flex justify-between text-green-600 font-medium" x-show="poinDiskon > 0">
                    <span>Diskon poin</span>
                    <span x-text="'-' + formatRupiah(poinDiskon)"></span>
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
                <a href="{{ route('orders.index') }}" class="flex-1 py-2 text-center text-xs text-gray-500 hover:text-gray-700 border border-gray-100 rounded-lg">Batal</a>
                <button type="submit" :disabled="submitting" class="flex-1 py-2 text-xs bg-brand text-white font-medium rounded-lg hover:bg-brand-hover disabled:opacity-50">
                    <span x-text="submitting ? 'Menyimpan...' : 'Simpan Order'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

</form>
</div>

@php
    $initialItems = old('items', [[
        'layanan_id' => '',
        'jenis_barang_id' => '',
        'jumlah_pasang' => 1,
        'merek' => '',
        'warna' => '',
        'kondisi' => '',
    ]]);
@endphp

<script>
function orderForm() {
    return {
        items: {{ Illuminate\Support\Js::from($initialItems) }}.map(item => ({
                layanan_id: '',
                jenis_barang_id: '',
                jumlah_pasang: 1,
                merek: '',
                warna: '',
                kondisi: '',
                hargaSatuan: 0,
                hargaLayananAsli: 0,
                hppSatuan: 0,
                ...item,
            })),
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
        lokasi_id: @js((string) old('lokasi_id', '')),
        hargaTambahan: 0,
        hargaCustom: false,
        lokasiLabel: '',
        hargaPerLayanan: {},
        voucherDiskon: 0,
        poinDiskon: 0,
        submitting: false,

        init() {
            this.$el.addEventListener('poin-toggled', (e) => {
                this.poinDiskon = e.detail.diskon || 0;
            });
            this.$el.addEventListener('voucher-changed', (e) => {
                this.voucherDiskon = e.detail.diskon || 0;
                this.poinDiskon = 0;
            });
            this.items.forEach(item => this.hitungHargaItem(item));
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
            return Math.max(0, this.subtotal - this.voucherDiskon - this.poinDiskon);
        },

        get estimasiTanggal() {
            // Dapatkan jumlah hari maksimum dari item yang dipilih
            let maxMenit = 2 * 1440;
            this.items.forEach(item => {
                if (item.layanan_id && this.layananMap[item.layanan_id]) {
                    maxMenit = Math.max(maxMenit, this.layananMap[item.layanan_id].menit);
                }
            });

            const d = new Date();
            d.setMinutes(d.getMinutes() + maxMenit);

            // Format to Y-m-d\TH:i for datetime-local
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const date = String(d.getDate()).padStart(2, '0');
            const h = String(d.getHours()).padStart(2, '0');
            const min = String(d.getMinutes()).padStart(2, '0');

            return `${y}-${m}-${date}T${h}:${min}`;
        },

        submitForm(e) {
            this.submitting = true;
        },

        formatRupiah(n) {
            if (!n && n !== 0) return 'Rp 0';
            const abs = Math.abs(Math.round(n));
            const formatted = 'Rp ' + abs.toLocaleString('id-ID');
            return n < 0 ? '-' + formatted : formatted;
        }
    };
}

function pelangganAuto(initialName = '') {
    return {
        nama: initialName,
        saran: [],
        terbuka: false,
        fokus: -1,
        timer: null,

        cari(val) {
            clearTimeout(this.timer);
            this.fokus = -1;
            if (val.length < 2) { this.saran = []; this.terbuka = false; return; }
            this.timer = setTimeout(async () => {
                try {
                    const res  = await fetch('/pelanggans/cari?q=' + encodeURIComponent(val));
                    this.saran = await res.json();
                    this.terbuka = this.saran.length > 0;
                } catch (e) {}
            }, 300);
        },

        pilih(p) {
            this.nama    = p.nama;
            this.saran   = [];
            this.terbuka = false;
            this.fokus   = -1;
            // Dispatch HP and poin info together
            window.dispatchEvent(new CustomEvent('set-no-hp', { detail: p.no_hp }));
            window.dispatchEvent(new CustomEvent('poin-loaded', { detail: { poin: p.poin ?? 0, nilai_rupiah: (p.poin ?? 0) * 100, nama: p.nama } }));
        },

        fokusItem(arah) {
            if (!this.terbuka || this.saran.length === 0) return;
            this.fokus = Math.max(0, Math.min(this.saran.length - 1, this.fokus + arah));
        },

        pilihFokus() {
            if (this.fokus >= 0 && this.saran[this.fokus]) this.pilih(this.saran[this.fokus]);
        },

        tutup() { this.terbuka = false; this.fokus = -1; }
    }
}

function voucherForm(initialCode = '') {
    return {
        kode: initialCode,
        valid: initialCode !== '',
        pesan: '',
        reset() {
            this.valid = false;
            this.pesan = '';
            this.$dispatch('voucher-changed', { diskon: 0 });
        },
        async cek(totalHarga = 0) {
            if (!this.kode) return;
            const res = await fetch(`/vouchers/cek?kode=${this.kode.toUpperCase()}&total=${totalHarga}`);
            const data  = await res.json();
            this.valid  = data.valid;
            this.pesan  = data.valid ? data.keterangan : data.pesan;
            this.$dispatch('voucher-changed', { diskon: data.valid ? (data.diskon ?? 0) : 0 });
        }
    }
}

function poinForm() {
    return {
        poinTersedia: 0,
        nilaiRupiah: 0,
        aktif: false,
        loaded: false,
        muat(data) {
            this.poinTersedia = data.poin ?? 0;
            this.nilaiRupiah  = data.nilai_rupiah ?? 0;
            this.loaded       = true;
            this.aktif        = false; // reset checkbox on new customer
            this.$dispatch('poin-toggled', { diskon: 0 });
        }
    }
}

function phoneInput(initialPhone = '') {
    return {
        noHp: initialPhone,
        pelangganDitemukan: false,
        namaDitemukan: '',
        poinDitemukan: 0,

        setNoHp(val) {
            this.noHp = val;
            this.cariPoin();
        },

        async cariPoin() {
            const hp = this.noHp.replace(/[\s\-\+]/g, '');
            if (hp.length < 8) {
                this.pelangganDitemukan = false;
                return;
            }
            try {
                const res  = await fetch('/pelanggans/poin?no_hp=' + encodeURIComponent(hp));
                const data = await res.json();
                if (data.found) {
                    this.pelangganDitemukan = true;
                    this.namaDitemukan      = data.nama;
                    this.poinDitemukan      = data.poin;
                    window.dispatchEvent(new CustomEvent('poin-loaded', { detail: data }));
                } else {
                    this.pelangganDitemukan = false;
                    window.dispatchEvent(new CustomEvent('poin-loaded', { detail: { poin: 0, nilai_rupiah: 0 } }));
                }
            } catch (e) { this.pelangganDitemukan = false; }
        },

        pakaiPelanggan() {
            // Auto-fill nama from the found customer via event
            window.dispatchEvent(new CustomEvent('set-nama-pelanggan', { detail: this.namaDitemukan }));
        }
    }
}
</script>
@endsection

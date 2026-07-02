@extends('layouts.app')
@section('title', 'Order baru')

@section('content')
<div class="max-w-2xl">
<div class="bg-white border border-gray-100 rounded-xl p-6">
<form method="POST" action="{{ route('orders.store') }}" x-data="orderForm()">
@csrf

{{-- Data pelanggan --}}
<div class="mb-6">
    <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Data pelanggan</h2>
    <div class="grid grid-cols-2 gap-4">
        <div x-data="pelangganAuto()" class="relative">
            <label class="block text-xs text-gray-500 mb-1.5">Nama pelanggan <span class="text-red-400">*</span></label>
            <input type="text" name="nama_pelanggan"
                   x-model="nama"
                   @input="cari($event.target.value)"
                   @keydown.escape="tutup()"
                   @keydown.arrow-down.prevent="fokusItem(+1)"
                   @keydown.arrow-up.prevent="fokusItem(-1)"
                   @keydown.enter.prevent="pilihFokus()"
                   @blur="setTimeout(() => tutup(), 150)"
                   autocomplete="off"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                   placeholder="Budi Santoso" required>
            {{-- Dropdown saran --}}
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
            @error('nama_pelanggan')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div x-data="{ noHp: '{{ old('no_hp', '') }}' }" @set-no-hp.window="noHp = $event.detail">
            <label class="block text-xs text-gray-500 mb-1.5">No. HP <span class="text-red-400">*</span></label>
            <input type="text" name="no_hp"
                   x-model="noHp"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                   placeholder="0812-xxxx-xxxx" required>
            @error('no_hp')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>
    <p class="text-xs text-gray-400 mt-2">Pelanggan otomatis terdaftar berdasarkan nomor HP.</p>
</div>

{{-- Lokasi + Layanan (saling mempengaruhi harga) --}}
<div class="mb-6">
    <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Lokasi & Layanan</h2>

    {{-- Pilih lokasi dulu --}}
    @if($lokasis->count() > 0)
    <div class="mb-4">
        <label class="block text-xs text-gray-500 mb-1.5">Lokasi penyimpanan sepatu</label>
        <select name="lokasi_id"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                @change="await pilihLokasi($event.target)">
            <option value="">-- Pilih lokasi (opsional) --</option>
            @foreach($lokasis as $lok)
            <option value="{{ $lok->id }}"
                    data-harga-custom="{{ $lok->harga_custom ? '1' : '0' }}"
                    data-harga-tambahan="{{ $lok->harga_tambahan }}"
                    data-label="{{ $lok->harga_tambahan_format }}"
                    {{ old('lokasi_id') == $lok->id ? 'selected' : '' }}>
                {{ $lok->kode }} — {{ $lok->nama }}
                @if($lok->harga_custom)
                    ({{ $lok->harga_tambahan_format }})
                @endif
            </option>
            @endforeach
        </select>
        {{-- Info harga lokasi --}}
        <p class="text-xs mt-1.5" x-show="lokasiLabel !== ''"
           :class="hargaTambahan > 0 ? 'text-amber-700' : (hargaTambahan < 0 ? 'text-green-700' : 'text-gray-400')"
           x-text="'Harga lokasi ini: ' + lokasiLabel">
        </p>
    </div>
    <div x-show="$data.lokasi_id" class="mb-4">
        <label class="block text-xs text-gray-500 mb-1.5">Catatan lokasi</label>
        <input type="text" name="catatan_lokasi" value="{{ old('catatan_lokasi') }}"
               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
               placeholder="Misal: baris ke-2 dari kiri">
    </div>
    @endif

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-xs text-gray-500 mb-1.5">Jenis sepatu <span class="text-red-400">*</span></label>
            <select name="jenis_sepatu"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                @foreach(['Sneakers', 'Running', 'Boots', 'Formal', 'Sandal', 'Lainnya'] as $j)
                <option value="{{ $j }}" {{ old('jenis_sepatu') === $j ? 'selected' : '' }}>{{ $j }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1.5">Jumlah pasang <span class="text-red-400">*</span></label>
            <input type="number" name="jumlah_pasang" value="{{ old('jumlah_pasang', 1) }}"
                   min="1" max="20"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                   x-model.number="jumlah" required>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1.5">Merek sepatu</label>
            <input type="text" name="merek" value="{{ old('merek') }}"
                   placeholder="Nike, Adidas, Vans..."
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1.5">Warna</label>
            <input type="text" name="warna" value="{{ old('warna') }}"
                   placeholder="Putih, Hitam, Merah..."
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
        </div>
    </div>

    <div class="mb-4">
        <label class="block text-xs text-gray-500 mb-1.5">Kondisi awal (detail kerusakan)</label>
        <input type="text" name="kondisi" value="{{ old('kondisi') }}"
               placeholder="Kotor parah, ada noda tinta, sol mengelupas..."
               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
    </div>

    <div class="mb-4">
        <label class="block text-xs text-gray-500 mb-1.5">Layanan <span class="text-red-400">*</span></label>
        <select name="layanan_id"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                @change="pilihLayanan($event.target)" required>
            <option value="">-- Pilih layanan --</option>
            @foreach($layanans as $l)
            <option value="{{ $l->id }}"
                    data-harga="{{ $l->harga }}"
                    data-hari="{{ $l->estimasi_hari }}"
                    data-hpp="{{ $hppLayanan[$l->id] ?? 0 }}"
                    {{ old('layanan_id') == $l->id ? 'selected' : '' }}>
                {{ $l->nama }} — Rp {{ number_format($l->harga, 0, ',', '.') }} / pasang
            </option>
            @endforeach
        </select>
        @error('layanan_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-xs text-gray-500 mb-1.5">Catatan kondisi sepatu</label>
        <textarea name="catatan" rows="2"
                  class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none"
                  placeholder="Misal: ada noda tinta di bagian kiri, sol mengelupas...">{{ old('catatan') }}</textarea>
    </div>
</div>

{{-- Estimasi & pembayaran --}}
<div class="mb-6">
    <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Estimasi & pembayaran</h2>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs text-gray-500 mb-1.5">Estimasi selesai <span class="text-red-400">*</span></label>
            <input type="date" name="estimasi_selesai"
                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                   :value="estimasiTanggal" required>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1.5">Metode bayar <span class="text-red-400">*</span></label>
            <select name="metode_bayar"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                <option value="tempo">Bayar saat ambil</option>
                <option value="cash">Cash (langsung)</option>
                <option value="qris">QRIS</option>
                <option value="transfer">Transfer (DP)</option>
                <option value="lunas">Sudah lunas</option>
            </select>
        </div>
    </div>

    {{-- Voucher --}}
    <div class="mt-4" x-data="voucherForm()">
        <label class="block text-xs text-gray-500 mb-1.5">Kode voucher (opsional)</label>
        <div class="flex gap-2">
            <input type="text" x-model="kode" @input="reset()" placeholder="HEMAT10"
                   style="text-transform:uppercase"
                   class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand">
            <button type="button" @click="cek($parent.total)"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700">
                Cek
            </button>
        </div>
        <input type="hidden" name="voucher_kode" :value="valid ? kode : ''">
        <p x-show="pesan" :class="valid ? 'text-green-700' : 'text-red-600'" class="text-xs mt-1.5 font-medium" x-text="pesan"></p>
    </div>
</div>

{{-- Total & profit preview --}}
<div class="border-t border-gray-100 pt-5">

    {{-- Breakdown harga --}}
    <div x-show="hargaSatuan > 0" class="bg-gray-50 rounded-xl p-4 mb-4 text-sm space-y-1.5">
        <div class="flex justify-between text-gray-600">
            <span>Harga layanan</span>
            <span x-text="formatRupiah(hargaLayananAsli)"></span>
        </div>
        <div x-show="hargaTambahan !== 0" class="flex justify-between"
             :class="hargaTambahan > 0 ? 'text-amber-700' : 'text-green-700'">
            <span x-text="hargaTambahan > 0 ? 'Biaya tambah lokasi' : 'Diskon lokasi'"></span>
            <span x-text="(hargaTambahan > 0 ? '+' : '') + formatRupiah(hargaTambahan)"></span>
        </div>
        <div class="flex justify-between font-medium text-gray-700 border-t border-gray-200 pt-1.5">
            <span>Harga efektif / pasang</span>
            <span x-text="formatRupiah(hargaSatuan)"></span>
        </div>
        <div class="flex justify-between font-medium text-gray-700">
            <span x-text="'× ' + jumlah + ' pasang'"></span>
            <span x-text="formatRupiah(total)"></span>
        </div>
    </div>

    <div class="flex items-start justify-between mb-5">
        <div>
            <p class="text-xs text-gray-400 mb-0.5">Total harga</p>
            <p class="text-2xl font-semibold text-gray-900" x-text="formatRupiah(total)">Rp 0</p>
        </div>
        <div x-show="hppTotal > 0" class="text-right">
            <p class="text-xs text-gray-400 mb-0.5">Est. Gross Profit</p>
            <p class="text-xl font-semibold text-green-700" x-text="formatRupiah(total - hppTotal)"></p>
            <p class="text-xs text-gray-400 mt-0.5" x-text="'Margin: ' + (total > 0 ? Math.round(((total - hppTotal) / total) * 100) : 0) + '%'"></p>
        </div>
    </div>
    <div class="flex gap-3 justify-end">
        <a href="{{ route('orders.index') }}" class="px-5 py-2.5 text-sm text-gray-500 hover:text-gray-900">Batal</a>
        <button type="submit" class="bg-brand text-white text-sm font-medium px-6 py-2.5 rounded-lg hover:bg-brand-hover">
            Simpan order
        </button>
    </div>
</div>

</form>
</div>
</div>

<script>
function orderForm() {
    const hppData = @json($hppLayanan);
    return {
        jumlah: @json(old('jumlah_pasang', 1)),
        hargaLayananAsli: 0,   // harga default dari layanan
        hargaSatuan: 0,        // harga yang dipakai (bisa override dari lokasi)
        hargaTambahan: 0,      // harga_tambahan lokasi (fallback)
        hppSatuan: 0,
        estimasiHari: 2,
        lokasi_id: '',
        layanan_id: '',
        lokasiLabel: '',
        hargaPerLayanan: {},   // map layanan_id => harga dari pivot lokasi_layanan

        get total()    { return this.jumlah * this.hargaSatuan; },
        get hppTotal() { return this.jumlah * this.hppSatuan; },

        get estimasiTanggal() {
            const d = new Date();
            d.setDate(d.getDate() + this.estimasiHari);
            return d.toISOString().split('T')[0];
        },

        // Hitung harga satuan berdasarkan kombinasi lokasi + layanan
        hitungHargaSatuan() {
            if (!this.hargaLayananAsli) return;
            // Cek per-service override dulu
            if (this.layanan_id && this.hargaPerLayanan[this.layanan_id] !== undefined) {
                this.hargaSatuan  = this.hargaPerLayanan[this.layanan_id];
                this.hargaTambahan = this.hargaSatuan - this.hargaLayananAsli;
                this.lokasiLabel  = this.hargaTambahan !== 0
                    ? 'Harga khusus lokasi ini'
                    : 'Sama dengan standar';
            } else {
                this.hargaSatuan = this.hargaLayananAsli + this.hargaTambahan;
            }
        },

        async pilihLokasi(select) {
            this.lokasi_id = select.value;
            this.hargaPerLayanan = {};
            this.hargaTambahan   = 0;
            this.lokasiLabel     = '';

            if (!select.value) {
                this.hargaSatuan = this.hargaLayananAsli;
                return;
            }

            // Ambil data harga dari API (termasuk harga_per_layanan)
            try {
                const url  = `/lokasi/${select.value}/harga?harga_layanan=${this.hargaLayananAsli}&layanan_id=${this.layanan_id}`;
                const res  = await fetch(url);
                const data = await res.json();

                this.hargaPerLayanan = data.harga_per_layanan || {};
                this.hargaTambahan   = data.harga_tambahan || 0;
                this.lokasiLabel     = data.label || '';
            } catch(e) {
                const opt = select.options[select.selectedIndex];
                const isCustom = opt.dataset.hargaCustom === '1';
                this.hargaTambahan = isCustom ? parseInt(opt.dataset.hargaTambahan || 0) : 0;
                this.lokasiLabel   = isCustom ? opt.dataset.label : '';
            }

            this.hitungHargaSatuan();
        },

        pilihLayanan(select) {
            const opt = select.options[select.selectedIndex];
            this.layanan_id       = select.value;
            this.hargaLayananAsli = parseInt(opt.dataset.harga || 0);
            this.estimasiHari     = parseInt(opt.dataset.hari  || 2);
            this.hppSatuan        = parseInt(opt.dataset.hpp   || 0);
            this.hitungHargaSatuan();
        },

        formatRupiah(n) {
            if (!n && n !== 0) return 'Rp 0';
            const abs = Math.abs(Math.round(n));
            const formatted = 'Rp ' + abs.toLocaleString('id-ID');
            return n < 0 ? '-' + formatted : formatted;
        }
    }
}

function pelangganAuto() {
    return {
        nama: '{{ old('nama_pelanggan', '') }}',
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
            window.dispatchEvent(new CustomEvent('set-no-hp', { detail: p.no_hp }));
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

function voucherForm() {
    return {
        kode: '',
        valid: false,
        pesan: '',
        diskon: 0,
        reset() { this.valid = false; this.pesan = ''; this.diskon = 0; },
        async cek(totalHarga = 0) {
            if (!this.kode) return;
            const res = await fetch(`/vouchers/cek?kode=${this.kode.toUpperCase()}&total=${totalHarga}`);
            const data  = await res.json();
            this.valid  = data.valid;
            this.pesan  = data.valid ? data.keterangan : data.pesan;
            this.diskon = data.diskon ?? 0;
        }
    }
}
</script>
@endsection

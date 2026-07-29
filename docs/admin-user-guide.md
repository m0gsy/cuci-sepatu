# Panduan Administrator — Step Shine Works

> Sistem manajemen shoe care berbasis web. Dibuat dengan Laravel 12, PHP 8.3, MySQL, Tailwind CSS v3, Alpine.js.

---

## Daftar Isi

1. [Dashboard](#1-dashboard)
2. [Autentikasi](#2-autentikasi)
3. [Manajemen Order](#3-manajemen-order)
4. [Manajemen Pelanggan](#4-manajemen-pelanggan)
5. [Master Layanan](#5-master-layanan)
6. [Master Lokasi](#6-master-lokasi)
7. [Voucher](#7-voucher)
8. [HPP (Harga Pokok Produksi)](#8-hpp-harga-pokok-produksi)
9. [Laporan](#9-laporan)
10. [Operasional](#10-operasional)
11. [Stok Bahan](#11-stok-bahan)
12. [Karyawan](#12-karyawan)
13. [Template WhatsApp](#13-template-whatsapp)
14. [Ulasan / Review](#14-ulasan--review)
15. [Reward & Poin](#15-reward--poin)
16. [Profil Pengguna](#16-profil-pengguna)
17. [Halaman Publik](#17-halaman-publik)

---

## 1. Dashboard

**Navigasi:** Setelah login → `/dashboard`

Dashboard menampilkan ringkasan bisnis secara real-time. Tampilan berbeda berdasarkan peran pengguna:

### Dashboard Owner / Admin

#### Stats Hari Ini

| Kartu | Keterangan |
|-------|-----------|
| Pendapatan Hari Ini | Total pembayaran berstatus `selesai` yang `dibayar_pada` hari ini |
| HPP Hari Ini | Total HPP order yang dibuat hari ini (status aktif) |
| Gross Profit Hari Ini | Pendapatan – HPP hari ini |
| Gross Margin | `(Gross Profit / Pendapatan) × 100%` |
| Order Hari Ini | Jumlah order dibuat hari ini |
| Dalam Proses | Order berstatus draft, menunggu pembayaran, diproses, atau siap diambil |
| Siap Diambil | Order berstatus `siap_diambil` |

#### Stats Bulan Ini

| Metrik | Keterangan |
|--------|-----------|
| Gross Sales | Total harga efektif × jumlah pasang (sebelum diskon) |
| Net Sales | Total pembayaran lunas bulan ini |
| HPP | Total HPP semua order bulan ini |
| Gross Profit | Net Sales – HPP |
| Gross Margin | `(Gross Profit / Net Sales) × 100%` |
| Jumlah Transaksi | Jumlah order aktif bulan ini |
| Avg per Transaksi | Net Sales / Jumlah Transaksi |
| Total Diskon | Gross Sales – Net Sales |

#### Grafik

- **Grafik Harian (7 hari):** Bar chart pendapatan lunas per hari, 7 hari terakhir.
- **Grafik Bulanan (12 bulan):** Line/bar chart pendapatan lunas per bulan, 12 bulan terakhir.

#### Top Layanan Bulan Ini

Tabel 10 layanan teratas berdasarkan gross sales, menampilkan: nama layanan, item sold (pasang), gross sales, net sales, gross profit, dan margin (%).

#### Order Terbaru

8 order paling baru lengkap dengan status, layanan, dan pembayaran.

#### Alert Stok Menipis

Muncul jika ada bahan dengan status `menipis` (stok ≤ minimum) atau `habis` (stok = 0).

#### Indikator Terlambat

Jumlah order aktif yang sudah melewati tanggal `estimasi_selesai`.

---

### Dashboard Cleaner

Cleaner melihat tampilan berbeda: **daftar semua order aktif** (draft, menunggu pembayaran, diproses, siap diambil) beserta jumlah order terlambat. Tidak ada data finansial.

---

## 2. Autentikasi

### Login

**URL:** `/login`

| Field | Keterangan |
|-------|-----------|
| Email | Email terdaftar di sistem |
| Password | Password minimal 8 karakter |

**Catatan:**
- Registrasi mandiri dinonaktifkan. Akun karyawan hanya bisa dibuat oleh Owner melalui menu Karyawan.
- Jika lupa password, gunakan link **"Lupa Password?"** di halaman login untuk menerima email reset.
- Setelah login berhasil, sistem mencatat `last_login` pada profil pengguna.

### Logout

Klik nama profil → **Logout** (atau klik tombol Logout jika tersedia di navigasi). Sesi dihapus secara server-side.

### Reset Password

1. Buka `/forgot-password`.
2. Masukkan alamat email.
3. Buka email → klik link reset.
4. Masukkan password baru (minimal 8 karakter, konfirmasi).

### Keamanan Sesi

- Akun yang di-nonaktifkan oleh Owner tidak bisa login.
- Semua halaman staf memerlukan autentikasi dan akun aktif.

---

## 3. Manajemen Order

**Navigasi:** Menu **Order** → `/orders`

**Hak akses:** Memerlukan permission `orders.manage`. Owner selalu memiliki akses ini.

### 3.1 Daftar Order

Halaman `/orders` menampilkan semua order dengan paginasi 15 per halaman.

**Filter yang tersedia:**
- **Status:** Dropdown filter berdasarkan status order
- **Cari:** Pencarian berdasarkan nama pelanggan, nomor order (`no_order`), atau nomor HP

**Kolom yang ditampilkan:**
- No. Order
- Pelanggan (nama + nomor HP)
- Layanan
- Jumlah Pasang
- Lokasi (jika ada)
- Total Bayar
- Status (badge berwarna)
- Estimasi Selesai + indikator terlambat

### 3.2 Buat Order Baru

**URL:** `/orders/create`

**Form input:**

| Field | Keterangan | Validasi |
|-------|-----------|---------|
| Nama Pelanggan | Nama lengkap pelanggan | Wajib, maks 100 karakter |
| No. HP | Nomor WhatsApp pelanggan | Wajib, maks 20 karakter |
| Layanan | Pilih dari daftar layanan aktif | Wajib |
| Jenis Sepatu | Contoh: Running, Casual, Boots | Wajib, maks 50 karakter |
| Merek | Opsional. Contoh: Nike, Adidas | Maks 50 karakter |
| Warna | Opsional. Contoh: Putih, Hitam | Maks 30 karakter |
| Kondisi | Opsional. Deskripsi kondisi awal | Maks 100 karakter |
| Jumlah Pasang | Minimal 1, maksimal 20 | Wajib, angka |
| Catatan | Instruksi khusus dari pelanggan | Maks 500 karakter |
| Estimasi Selesai | Tanggal perkiraan selesai | Wajib, tidak boleh sebelum hari ini |
| Metode Bayar | tempo / transfer / lunas / cash / qris | Wajib |
| Lokasi | Pilihan lokasi pengiriman/antar (opsional) | Harus ada di tabel lokasi aktif |
| Catatan Lokasi | Keterangan alamat/instruksi antar | Maks 200 karakter |
| Kode Voucher | Kode diskon (opsional) | Maks 30 karakter |
| HPP Override | Override HPP manual (opsional) | Integer ≥ 0 |

**Alur saat menyimpan:**
1. Sistem menghitung `harga_satuan` dari layanan (atau harga lokasi jika ada override).
2. Total = harga_satuan × jumlah_pasang.
3. Jika kode voucher valid: diskon diterapkan, total dikurangi diskon.
4. Pelanggan dicari berdasarkan `no_hp`. Jika tidak ditemukan, data pelanggan baru dibuat otomatis.
5. Nomor order di-generate otomatis: format `ORD-YYYYMMDD-XXXXXX` dengan suffix acak dan unique constraint.
6. Token publik 32 karakter di-generate untuk tracking publik.
7. Status awal: `draft`.
8. Record pembayaran dibuat. Status `lunas` jika metode bayar = lunas/cash/qris.
9. Notifikasi WhatsApp **Order Masuk** dikirim ke nomor pelanggan (async via queue).

**Catatan metode bayar:**
- `lunas`, `cash`, `qris` → pembayaran langsung ditandai lunas
- `tempo`, `transfer` → status pembayaran `belum_selesai`, tandai lunas manual nanti

### 3.3 Detail Order

**URL:** `/orders/{id}`

Halaman detail menampilkan semua informasi order: data sepatu, pembayaran, status tracking, lokasi, voucher, dan review pelanggan (jika ada).

**Aksi yang tersedia dari halaman detail:**

| Tombol | Fungsi |
|--------|--------|
| Edit Order | Ubah data order (tidak bisa jika status sudah selesai) |
| Update Status | Ubah status ke tahap berikutnya atau tahap lain |
| Tandai Lunas | Ubah status pembayaran menjadi lunas |
| Cetak Nota | Unduh nota PDF format termal (80mm) |
| Kirim WA | Kirim ulang notifikasi WhatsApp ke pelanggan |
| Kirim Invoice WA | Kirim pesan invoice + link status publik |
| Update Lokasi | Ubah atau tambahkan lokasi order |

### 3.4 Update Status Order

**URL:** `PATCH /orders/{id}/status`

Status order mengikuti alur maju berikut:

```
draft → menunggu_pembayaran → diproses → siap_diambil → selesai
```

**Trigger otomatis saat update status:**

| Status | Aksi Otomatis |
|--------|--------------|
| `diproses` | Kirim WA notifikasi "Mulai Diproses" ke pelanggan |
| `siap_diambil` | Kirim WA "Order Selesai", tampilkan estimasi poin, set `selesai_pada` |
| `selesai` | Tandai pembayaran lunas dan tambahkan poin satu kali |
| `batal` | Kembalikan stok, poin yang ditukar, dan kuota voucher secara idempoten |

**Aturan bisnis:**
- Order berstatus `siap_diambil`, `selesai`, atau `batal` tidak bisa diedit.
- Status hanya dapat bergerak maju. Pembatalan hanya tersedia dari `draft` atau `menunggu_pembayaran`.

### 3.5 Tandai Lunas

**URL:** `PATCH /orders/{id}/tandai-lunas`

Mengubah status pembayaran dari `belum_selesai` menjadi `selesai` dan mengisi `dibayar_pada` dengan waktu sekarang. Jika pembayaran sudah lunas, aksi ini mengembalikan pesan error.

### 3.6 Cetak Nota

**URL:** `GET /orders/{id}/nota`

Menghasilkan file PDF format nota termal (lebar 226.77pt / ~80mm). File di-stream langsung ke browser. Nota berisi: nomor order, detail sepatu, layanan, pembayaran, voucher (jika ada).

### 3.7 Kirim Ulang WhatsApp

**URL:** `POST /orders/{id}/kirim-wa`

- Jika status `siap_diambil`: kirim pesan **order_selesai**
- Status lain: kirim pesan **order_masuk**

### 3.8 Kirim Invoice WhatsApp

**URL:** `POST /orders/{id}/kirim-invoice`

Mengirim pesan invoice (template `invoice`) beserta link status publik ke nomor pelanggan.

### 3.9 Edit Order

**URL:** `GET/PUT /orders/{id}/edit`

Tidak bisa mengedit order berstatus `siap_diambil`, `selesai`, atau `batal`. Field yang dapat diedit sama dengan form create, kecuali voucher (tidak bisa ubah voucher setelah order dibuat).

Saat layanan atau jumlah pasang berubah, HPP di-recalculate otomatis.

---

## 4. Manajemen Pelanggan

**Navigasi:** Menu **Pelanggan** → `/pelanggans`

**Hak akses:** Memerlukan permission `pelanggan`.

### 4.1 Daftar Pelanggan

Menampilkan semua pelanggan dengan paginasi 15 per halaman, diurutkan berdasarkan jumlah order terbanyak.

**Kolom:** Nama, No. HP, Tier, Poin, Jumlah Order, Total Belanja, Order Terakhir.

**Filter:** Pencarian nama atau nomor HP.

### 4.2 Tambah Pelanggan

**Catatan:** Pelanggan baru dibuat otomatis saat order dibuat (berdasarkan nomor HP). Form ini untuk input pelanggan manual.

| Field | Validasi |
|-------|---------|
| Nama | Wajib, maks 100 karakter |
| No. HP | Wajib, 8-15 digit, unik |
| Alamat | Opsional, maks 200 karakter |
| Catatan | Opsional, maks 500 karakter |

### 4.3 Detail Pelanggan

**URL:** `/pelanggans/{id}`

Menampilkan:
- **Statistik:** Total order, total belanja lunas, total pasang, layanan favorit.
- **Poin dan Tier:** Poin saat ini, tier membership (reguler/silver/gold/platinum).
- **Rekap per Layanan:** Jumlah order dan total pasang per layanan.
- **Riwayat Order:** Semua order pelanggan dengan paginasi 10 per halaman.

### 4.4 Edit Pelanggan

Bisa mengubah nama, nomor HP, alamat, dan catatan. Nomor HP harus tetap unik.

### 4.5 Sistem Tier Pelanggan

Tier dihitung berdasarkan **total belanja lunas** sepanjang waktu:

| Tier | Minimal Total Belanja |
|------|----------------------|
| Reguler | < Rp 500.000 |
| Silver | ≥ Rp 500.000 |
| Gold | ≥ Rp 2.000.000 |
| Platinum | ≥ Rp 5.000.000 |

Tier diperbarui otomatis ketika order mencapai status `selesai`.

### 4.6 Sistem Poin Pelanggan

- **Earn:** 1 poin per Rp 10.000 yang dibayar (dihitung dari `pembayaran.total`).
- Poin ditambahkan otomatis satu kali saat status berubah ke `selesai`.
- Poin bisa ditukar dengan reward yang tersedia.
- Riwayat poin dicatat di tabel `poin_histories`.

---

## 5. Master Layanan

**Navigasi:** Menu **Layanan** → `/layanans`

**Hak akses:** Memerlukan permission `layanans`.

### 5.1 Daftar Layanan

Menampilkan semua layanan (aktif dan nonaktif) beserta jumlah komponen HPP dan total HPP per layanan.

**Layanan default dari sistem:**

| Nama | Harga | Estimasi |
|------|-------|---------|
| Cuci Biasa | Rp 25.000 | 2 hari |
| Deep Clean | Rp 50.000 | 3 hari |
| Premium | Rp 100.000 | 4 hari |
| Repaint | Rp 120.000 | 5 hari |
| Cuci Sandal | Rp 15.000 | 1 hari |

### 5.2 Tambah Layanan

| Field | Validasi |
|-------|---------|
| Nama | Wajib, unik, maks 100 karakter |
| Harga | Wajib, minimal Rp 1.000 |
| Estimasi Hari | Wajib, 1–30 hari |

Status aktif secara default. Layanan nonaktif tidak muncul di form order.

### 5.3 Edit Layanan

Mengubah nama, harga, dan estimasi hari.

**Catatan:** Mengubah harga layanan tidak mengubah harga order yang sudah ada (order menyimpan `harga_satuan` sendiri).

### 5.4 Aktif / Nonaktif Layanan

Toggle status aktif. Layanan yang dinonaktifkan tidak bisa dipilih di form order baru, tapi order lama tetap menggunakan layanan tersebut.

---

## 6. Master Lokasi

**Navigasi:** Menu **Lokasi** → `/lokasi`

**Hak akses:** Memerlukan permission `lokasi`.

Lokasi digunakan untuk antar-jemput atau cabang yang memiliki harga berbeda.

### 6.1 Daftar Lokasi

Menampilkan semua lokasi dengan jumlah order aktif dan selesai. Juga menampilkan daftar order aktif per lokasi.

### 6.2 Tambah Lokasi

| Field | Keterangan | Validasi |
|-------|-----------|---------|
| Kode | Kode singkat unik (misal: LOK-A) | Wajib, unik, maks 20 karakter |
| Nama | Nama lengkap lokasi | Wajib, maks 100 karakter |
| Deskripsi | Keterangan alamat atau lokasi | Maks 300 karakter |
| Harga Custom | Toggle: aktifkan harga tambahan | Boolean |
| Harga Tambahan | Tambahan harga (Rp) di atas harga standar | Integer |

### 6.3 Sistem Harga Lokasi

Ada dua cara penetapan harga lokasi:

**1. Harga Tambahan (Global):**
Semua layanan di lokasi ini dikenakan harga layanan + harga_tambahan.

**2. Override Per Layanan:**
Harga spesifik untuk satu layanan di satu lokasi. Melalui **menu Harga Layanan** di detail lokasi.

**Prioritas harga:**
1. Override per layanan (jika ada)
2. Harga tambahan global lokasi (jika `harga_custom = true`)
3. Harga standar layanan (fallback)

### 6.4 Set Harga Per Layanan

**URL:** `/lokasi/{id}/harga`

Tambah override harga untuk layanan tertentu di lokasi ini. Bisa hapus override (kembali ke harga standar).

### 6.5 Toggle Aktif Lokasi

Lokasi nonaktif tidak muncul di form order baru.

---

## 7. Voucher

**Navigasi:** Menu **Voucher** → `/vouchers`

**Hak akses:** Memerlukan permission `vouchers`.

### 7.1 Daftar Voucher

Menampilkan semua voucher beserta status, jumlah terpakai, dan batas kuota.

**Status voucher:**

| Status | Kondisi |
|--------|---------|
| Aktif | `aktif=true`, belum expired, kuota belum habis |
| Nonaktif | `aktif=false` |
| Expired | Tanggal `expired_at` sudah lewat |
| Habis | `terpakai >= kuota` |

### 7.2 Tambah Voucher

| Field | Keterangan | Validasi |
|-------|-----------|---------|
| Kode | Kode unik voucher (otomatis UPPERCASE) | Wajib, unik, maks 30 karakter |
| Tipe | `persen` atau `nominal` | Wajib |
| Nilai | Persentase (%) atau nominal (Rp) | Wajib, ≥ 1 |
| Expired At | Tanggal kadaluarsa | Opsional, harus setelah kemarin |
| Minimal Transaksi | Total minimum untuk pakai voucher | Opsional, default 0 |
| Kuota | Batas penggunaan total | Opsional (kosong = unlimited) |
| Deskripsi | Keterangan voucher | Maks 200 karakter |

### 7.3 Edit Voucher

Mengubah semua field kecuali kode voucher (kode tidak bisa diubah setelah dibuat).

### 7.4 Hapus Voucher

Hanya bisa menghapus voucher yang **belum pernah digunakan**. Jika voucher sudah dipakai di minimal 1 order, hapus tidak diizinkan.

### 7.5 Cara Kerja Voucher

- Saat input order, masukkan kode voucher di field **Kode Voucher**.
- Sistem memvalidasi: aktif, belum expired, kuota belum habis, dan total memenuhi minimum transaksi.
- Diskon dihitung:
  - Tipe `persen`: `total × nilai / 100`
  - Tipe `nominal`: `min(nilai, total)`
- Jika tidak valid, order tetap disimpan tanpa diskon (muncul pesan peringatan).
- Voucher yang berhasil digunakan: `terpakai` bertambah 1.

---

## 8. HPP (Harga Pokok Produksi)

**Navigasi:** Menu **HPP** → `/hpp`

**Hak akses:** Hanya **Owner** (`owner` middleware). Laporan HPP memerlukan permission `hpp`.

### 8.1 Master HPP per Layanan

Halaman `/hpp` menampilkan semua layanan beserta komponen HPP masing-masing.

**Komponen HPP** adalah biaya bahan/proses yang dibutuhkan untuk satu order layanan tersebut.

Contoh untuk layanan "Deep Clean":
- Sabun khusus: Rp 5.000
- Sikat: Rp 2.000
- Microfiber: Rp 1.500
- Listrik: Rp 2.500
- **Total HPP: Rp 11.000**

### 8.2 Tambah Komponen HPP

| Field | Validasi |
|-------|---------|
| Layanan | Pilih layanan | Wajib |
| Komponen | Nama komponen biaya | Wajib, maks 100 karakter |
| Biaya | Nominal biaya (Rp) | Wajib, ≥ 0 |

### 8.3 Edit dan Hapus Komponen

Bisa edit atau hapus komponen HPP individual.

**Catatan:** Perubahan HPP **tidak memengaruhi order yang sudah ada**. HPP tersimpan di setiap order saat order dibuat.

### 8.4 Laporan Profit/Loss

**URL:** `/hpp/laporan`

**Hak akses:** Permission `hpp`.

Menampilkan laporan per bulan:
- **Ringkasan:** Gross Sales, Net Sales, HPP, Gross Profit, Diskon, Gross Margin (%).
- **Rekap per Layanan:** Item sold, gross sales, net sales, HPP, gross profit, margin per layanan.
- **Rekap per Lokasi:** Jumlah order, net sales, HPP, gross profit per lokasi.
- **Detail Order:** Tabel semua order bulan tersebut.

---

## 9. Laporan

**Navigasi:** Menu **Laporan** → `/laporan`

**Hak akses:** Memerlukan permission `laporan`.

### 9.1 Laporan Bulanan

Filter berdasarkan bulan (`YYYY-MM`). Default: bulan berjalan.

**Rekap per Layanan:**
- Nama layanan, harga satuan, jumlah pasang, total pendapatan, persentase dari total.

**Total Bulan:** Total semua pembayaran lunas pada bulan tersebut.

**Detail Order:** Tabel semua order bulan tersebut (paginasi 50 per halaman).

### 9.2 Export PDF

**URL:** `GET /laporan/export-pdf?bulan=YYYY-MM`

Mengunduh laporan bulan tersebut dalam format PDF A4 portrait, berisi rekap dan detail order.

### 9.3 Export Excel

**URL:** `GET /laporan/export-excel?bulan=YYYY-MM`

Mengunduh file `.xlsx` dengan 2 sheet:
- **Sheet 1 "Rekap Bulanan":** Rekap per layanan dengan persentase.
- **Sheet 2 "Detail Order":** Semua order dengan warna status.

---

## 10. Operasional

**Navigasi:** Menu **Operasional** → `/operasional`

**Hak akses:** Memerlukan permission `operasional`.

### 10.1 Daftar Biaya Operasional

Filter berdasarkan bulan dan kategori. Menampilkan:
- Total biaya operasional bulan ini
- Pendapatan (lunas) bulan ini
- Rekap per kategori

### 10.2 Catat Biaya

| Field | Keterangan | Validasi |
|-------|-----------|---------|
| Nama | Deskripsi biaya | Wajib, maks 200 karakter |
| Kategori | Jenis biaya | Wajib |
| Jumlah | Nominal (Rp) | Wajib, ≥ 1 |
| Tanggal | Tanggal pengeluaran | Wajib |
| Catatan | Keterangan tambahan | Maks 500 karakter |

**Kategori biaya:**

| Kategori | Deskripsi |
|----------|-----------|
| `bahan` | Pembelian bahan habis pakai |
| `utilitas` | Listrik, air, internet |
| `gaji` | Gaji karyawan |
| `sewa` | Sewa tempat |
| `peralatan` | Pembelian alat |
| `lainnya` | Biaya lain-lain |

### 10.3 Hapus Biaya

Biaya operasional bisa dihapus permanen. Tidak ada fitur edit (hapus dan input ulang).

---

## 11. Stok Bahan

**Navigasi:** Menu **Stok** → `/stok`

**Hak akses:** Memerlukan permission `stok`.

### 11.1 Daftar Stok

Menampilkan semua bahan dengan status:

| Status | Kondisi | Warna Badge |
|--------|---------|-------------|
| Aman | Stok > minimum | Hijau |
| Menipis | Stok ≤ minimum (dan > 0) | Amber/Kuning |
| Habis | Stok = 0 | Merah |

**Nilai stok** = stok_saat_ini × harga_satuan.

**Bahan default dari sistem:**
- Sabun cuci khusus (liter), Sikat sol (pcs), Kain microfiber (pcs), Cat sepatu putih (botol), Polish/semir (kaleng), Kantong plastik (pcs), Kertas nota thermal (roll)

### 11.2 Tambah Bahan

| Field | Validasi |
|-------|---------|
| Nama | Wajib, unik, maks 100 karakter |
| Satuan | Wajib, maks 30 karakter (pcs, liter, botol, dll.) |
| Stok Saat Ini | Wajib, ≥ 0 |
| Stok Minimum | Wajib, ≥ 0 (batas alert) |
| Harga Satuan | Wajib, ≥ 0 (Rp) |
| Catatan | Maks 300 karakter |

### 11.3 Edit Bahan

Mengubah nama, satuan, stok minimum, harga satuan, dan catatan. Stok saat ini hanya bisa diubah melalui mutasi.

### 11.4 Mutasi Stok

**URL:** `POST /stok/{id}/mutasi`

| Field | Keterangan |
|-------|-----------|
| Tipe | `masuk` (tambah), `keluar` (kurangi), `penyesuaian` (set langsung) |
| Jumlah | Jumlah mutasi |
| Keterangan | Alasan mutasi |

**Kalkulasi stok:**
- `masuk`: stok baru = stok lama + jumlah
- `keluar`: stok baru = max(0, stok lama - jumlah) — tidak bisa minus
- `penyesuaian`: stok baru = jumlah (langsung set)

Setiap mutasi dicatat di riwayat (stok sebelum, sesudah, tipe, user, keterangan).

### 11.5 Riwayat Mutasi

**URL:** `/stok/{id}/riwayat`

Menampilkan semua riwayat mutasi stok dengan paginasi 20 per halaman, termasuk: tipe, jumlah, stok sebelum/sesudah, keterangan, pencatat, dan waktu.

---

## 12. Karyawan

**Navigasi:** Menu **Karyawan** → `/karyawans`

**Hak akses:** Hanya **Owner** (`owner` middleware).

### 12.1 Daftar Karyawan

Menampilkan semua pengguna sistem dengan:
- Nama, email, role, status aktif
- Jumlah order hari ini dan bulan ini
- Aksi: detail, edit, toggle aktif, reset password

**Pengurutan:** Aktif dulu, kemudian berdasarkan role dan nama.

### 12.2 Tambah Karyawan

| Field | Validasi |
|-------|---------|
| Nama | Wajib, maks 100 karakter |
| Email | Wajib, format email, unik |
| Password | Wajib, minimal 8 karakter, dikonfirmasi |
| Role | `owner` / `admin` / `cleaner` |
| No. HP | Opsional, format angka 8-15 digit |
| Alamat | Opsional, maks 200 karakter |

Akun baru langsung aktif. Karyawan harus login sendiri pertama kali.

### 12.3 Edit Karyawan

Mengubah nama, email, role, nomor HP, dan alamat. Tidak bisa mengedit akun sendiri via halaman ini (gunakan halaman Profil).

### 12.4 Reset Password Karyawan

Input password baru + konfirmasi. Password lama tidak diperlukan (hak owner).

### 12.5 Aktif / Nonaktif Karyawan

Toggle status akun aktif/nonaktif. **Aturan:**
- Tidak bisa menonaktifkan akun sendiri.
- Tidak bisa menonaktifkan owner terakhir yang masih aktif (minimal 1 owner aktif).
- Akun nonaktif tidak bisa login.

### 12.6 Manajemen Hak Akses

**URL:** `POST /karyawans/permissions`

Owner dapat mengatur permission untuk role `admin` dan `cleaner` secara massal. Daftar permission yang bisa diatur:

| Key Permission | Akses |
|---------------|-------|
| `orders.manage` | Input / edit order & pembayaran |
| `pelanggan` | Data pelanggan |
| `lokasi` | Lokasi sepatu |
| `laporan` | Laporan penjualan |
| `hpp` | Profit / Loss & HPP |
| `layanans` | Master layanan & harga |
| `vouchers` | Voucher |
| `rewards` | Reward & Poin |
| `stok` | Stok bahan |
| `operasional` | Operasional |

**Catatan penting:**
- Permission disimpan di tabel `role_permissions` per role (bukan per user).
- Semua admin memiliki hak yang sama; semua cleaner memiliki hak yang sama.
- Owner selalu memiliki akses penuh ke semua fitur.
- Permission di-cache selama 5 menit. Perubahan efektif setelah cache expired atau sesi baru.
- Permission `wa_template` (template WhatsApp) tidak ada di UI manajemen permission — dikelola terpisah.

### 12.7 Detail Karyawan

**URL:** `/karyawans/{id}`

Menampilkan statistik kinerja karyawan: total order dibuat, order bulan ini, order hari ini, total nilai diproses (lunas), dan grafik rekap bulanan 6 bulan terakhir.

---

## 13. Template WhatsApp

**Navigasi:** Menu **Template WA** → `/wa-templates`

**Hak akses:** Memerlukan permission `wa_template`.

### 13.1 Daftar Template

Menampilkan semua template pesan WhatsApp yang tersedia.

**Template tersedia:**

| Kode | Nama | Trigger |
|------|------|---------|
| `order_masuk` | Order Masuk | Saat order baru dibuat |
| `mulai_dicuci` | Mulai Diproses | Saat status berubah ke `diproses` |
| `order_selesai` | Order Selesai | Saat status berubah ke `siap_diambil` |
| `invoice` | Invoice | Saat klik "Kirim Invoice WA" |

### 13.2 Edit Template

**URL:** `/wa-templates/{id}/edit`

Textarea untuk mengedit isi pesan. **Variabel yang tersedia** ditampilkan di halaman edit.

**Variabel per template:**

**`order_masuk`:**
- `{nama_pelanggan}`, `{no_order}`, `{layanan}`, `{lokasi}`, `{jumlah_pasang}`, `{total}`, `{metode_bayar}`, `{estimasi_selesai}`, `{link_status}`

**`mulai_dicuci`:**
- `{nama_pelanggan}`, `{no_order}`, `{layanan}`, `{lokasi}`, `{link_status}`

**`order_selesai`:**
- `{nama_pelanggan}`, `{no_order}`, `{layanan}`, `{lokasi}`, `{total}`, `{status_bayar}`, `{poin}`, `{link_status}`

**`invoice`:**
- `{nama_pelanggan}`, `{no_order}`, `{tanggal}`, `{layanan}`, `{lokasi}`, `{jenis_sepatu}`, `{jumlah_pasang}`, `{harga_satuan}`, `{total}`, `{metode_bayar}`, `{status_bayar}`

**Catatan format:**
- Gunakan `*teks*` untuk **bold** di WhatsApp.
- Gunakan `_teks_` untuk *italic* di WhatsApp.
- Baris yang nilai variabelnya kosong (misal lokasi kosong) akan dihapus otomatis dari pesan.

### 13.3 Reset ke Default

Tombol **Reset** mengembalikan template ke teks default bawaan sistem.

### 13.4 Konfigurasi WhatsApp

WhatsApp menggunakan layanan **Twilio**. Konfigurasi di file `.env`:

```
TWILIO_SID=ACxxxxxxxx
TWILIO_AUTH_TOKEN=isi_token_dari_twilio
TWILIO_WHATSAPP_FROM=+14155238886
```

Jika kredensial Twilio kosong, pengiriman WA dilewati dan peringatan dicatat di log.

---

## 14. Ulasan / Review

**Navigasi:** Menu **Reviews** → `/reviews`

**Hak akses:** Admin atau Owner (`admin-or-owner` middleware).

### 14.1 Daftar Review

Menampilkan semua ulasan pelanggan dengan paginasi 20 per halaman.

**Kolom:** No. Order, Pelanggan, Rating (1–5 bintang), Ulasan, Layanan, Tanggal.

### 14.2 Cara Pelanggan Memberikan Review

Review diberikan melalui halaman publik (bukan login):

1. Pelanggan menerima link status order via WhatsApp: `https://domain.com/status/{token_publik}`.
2. Pelanggan membuka link dan melihat status order.
3. Jika order sudah `siap_diambil` atau `selesai`, form rating muncul.
4. Pelanggan memilih rating 1–5 bintang dan mengisi ulasan opsional.
5. Review hanya bisa diberikan **1 kali** per order.

---

## 15. Reward & Poin

**Navigasi:** Menu **Reward** → `/rewards` dan **Kelola Poin** → `/rewards/poin`

**Hak akses:** Memerlukan permission `rewards`.

### 15.1 Master Reward

Halaman `/rewards` menampilkan daftar reward yang tersedia untuk ditukar pelanggan.

**Default reward dari sistem:**
- Diskon 10% 1 pasang (50 poin)
- Gratis cuci 1 pasang (100 poin)
- Upgrade ke Deep Clean (75 poin)
- Diskon 20% all item (150 poin)

### 15.2 Tambah Reward

| Field | Validasi |
|-------|---------|
| Nama | Wajib, unik, maks 100 karakter |
| Poin Dibutuhkan | Wajib, ≥ 1 |
| Deskripsi | Maks 300 karakter |

### 15.3 Toggle Aktif Reward

Reward nonaktif tidak ditampilkan ke pelanggan (jika ada halaman publik reward).

### 15.4 Hapus Reward

Hapus permanen reward.

### 15.5 Kelola Poin Manual

**URL:** `/rewards/poin`

Halaman ini memungkinkan Owner/Admin menambah atau mengurangi poin pelanggan secara manual.

| Field | Keterangan |
|-------|-----------|
| Pilih Pelanggan | Dropdown daftar pelanggan |
| Tipe | `tambah` atau `kurang` |
| Poin | Jumlah poin |
| Keterangan | Alasan perubahan poin (wajib) |

**Aturan:**
- Jika tipe `kurang` dan poin tidak cukup, aksi ditolak dengan pesan error.
- Semua perubahan dicatat di `poin_histories`.

**20 riwayat poin terbaru** ditampilkan di bawah form.

---

## 16. Profil Pengguna

**Navigasi:** Klik nama di header → **Profil** → `/profil`

Semua pengguna (owner, admin, cleaner) bisa mengedit profil sendiri:

| Field | Keterangan |
|-------|-----------|
| Nama | Nama tampilan |
| Email | Email login |
| Password | Opsional — kosongkan jika tidak ingin ubah |

---

## 17. Halaman Publik

Halaman berikut dapat diakses tanpa login (untuk pelanggan dan keperluan Meta Business Verification):

| URL | Halaman |
|-----|---------|
| `/` | Beranda Step Shine Works |
| `/about` | Tentang Kami |
| `/contact` | Kontak & Form Pesan |
| `/privacy-policy` | Kebijakan Privasi |
| `/terms` | Syarat & Ketentuan |
| `/refund-policy` | Kebijakan Refund |
| `/status/{token}` | Tracking order publik (per token) |

### Tracking Order Publik

Pelanggan dapat cek status order via link yang dikirim di WhatsApp. Halaman ini menampilkan status terkini, detail layanan, dan form review jika order sudah selesai.

---

*Terakhir diperbarui: 3 Juli 2026*
2

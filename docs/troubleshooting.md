# Troubleshooting — Step Shine Works

Panduan mengatasi masalah umum pada sistem Step Shine Works.

---

## 1. Login Gagal

### Gejala
Muncul pesan "Email atau password salah" / tidak bisa masuk.

### Kemungkinan Penyebab & Solusi

| Penyebab | Solusi |
|----------|--------|
| Password salah | Gunakan "Lupa Password?" untuk reset via email |
| Akun dinonaktifkan | Hubungi Owner untuk mengaktifkan kembali akun |
| Email belum diverifikasi | Cek kotak masuk email, klik link verifikasi |
| Caps Lock aktif | Pastikan Caps Lock tidak aktif saat mengetik password |
| Browser auto-fill isi password lama | Hapus data tersimpan di browser, masukkan password manual |

**Untuk Owner:** Cek status akun di menu **Karyawan** → pastikan kolom Aktif menampilkan status aktif.

---

## 2. CSS / Tampilan Tidak Load (Halaman Kacau)

### Gejala
Halaman muncul tanpa styling, tampilan tidak rapi, atau hanya teks mentah.

### Penyebab & Solusi

**Untuk pengembangan lokal:**
```bash
npm install
npm run dev
```
Pastikan Vite berjalan (`npm run dev`) saat development.

**Untuk production:**
```bash
npm run build
```
File asset harus di-build terlebih dahulu. File manifest Vite harus ada di `public/build/`.

**Jika masalah tetap:**
- Bersihkan cache browser (Ctrl+Shift+R / Cmd+Shift+R)
- Cek apakah file `public/build/manifest.json` ada
- Jalankan `php artisan config:clear && php artisan view:clear`

---

## 3. Notifikasi WhatsApp Tidak Terkirim

### Gejala
Pelanggan tidak menerima WA setelah order dibuat atau status diperbarui.

### Langkah Diagnosa

**Langkah 1: Cek konfigurasi**
Buka file `.env` dan pastikan:
```
TWILIO_SID=ACxxxxxxxx
TWILIO_AUTH_TOKEN=isi_token_anda
TWILIO_WHATSAPP_FROM=+14155238886
```
Jika kredensial Twilio kosong, WA tidak dikirim dan peringatan dicatat di log.

**Langkah 2: Cek log**
Buka `storage/logs/laravel.log` dan cari baris dengan kata "WA":
```
WA: kredensial Twilio belum lengkap
WA gagal: {"status":false, ...}
KirimWaJob gagal permanen ke 0812xxx
```

**Langkah 3: Cek queue worker**
Pesan WA dikirim secara async (antrian). Jika `QUEUE_CONNECTION=sync` di `.env`, WA dikirim langsung (tidak perlu worker). Jika `database` atau `redis`, pastikan worker berjalan:
```bash
php artisan queue:work
```

**Langkah 4: Cek nomor HP**
Nomor HP pelanggan harus valid (8–20 digit). Sistem mengonversi nomor `08xxx` menjadi `628xxx` otomatis.

**Langkah 5: Kirim ulang manual**
Buka detail order → klik **Kirim Ulang WA**.

### Solusi per Error

| Error di Log | Solusi |
|-------------|--------|
| Kredensial Twilio belum lengkap | Isi ketiga variabel `TWILIO_*`, restart queue worker |
| Respons Twilio gagal | Periksa saldo/kuota akun Twilio, sender WhatsApp, dan nomor tujuan |
| `HTTP timeout` | API Twilio timeout — coba lagi nanti atau periksa status Twilio |
| `KirimWaJob gagal permanen` | Semua 3 percobaan gagal — cek log detail, kirim ulang manual |

---

## 4. Status Order Tidak Bisa Diupdate

### Gejala
Tombol update status tidak muncul atau tidak berfungsi.

### Penyebab & Solusi

| Penyebab | Solusi |
|----------|--------|
| Tidak punya permission `orders.manage` | Hubungi Owner untuk menambahkan permission |
| Order sudah berstatus terminal | Status hanya dapat bergerak maju; `selesai` dan `batal` tidak dapat diubah |
| Masalah JavaScript | Bersihkan cache browser, atau coba browser lain |

---

## 5. Gross Sales / Net Sales Salah di Laporan

### Gejala
Angka di laporan tidak sesuai ekspektasi.

### Penjelasan Kalkulasi

Sistem menggunakan perhitungan berikut:

| Metrik | Rumus |
|--------|-------|
| Gross Sales | `harga_satuan × jumlah_pasang` (per order, sebelum diskon) |
| Net Sales | `pembayaran.total` (setelah diskon, yang benar-benar dibayar) |
| Diskon | Gross Sales – Net Sales |
| HPP | Tersimpan per order saat dibuat |
| Gross Profit | Net Sales – HPP |

**Order lama tanpa `harga_satuan`:** Sistem fallback ke `layanan.harga` ditambah `lokasi.harga_tambahan` (backward compat). Ini bisa menyebabkan gross sales berbeda jika harga layanan sudah diubah setelah order dibuat.

**Pendapatan hari ini vs bulan ini:** "Pendapatan" menggunakan `dibayar_pada` (tanggal pelunasan), bukan `created_at` (tanggal order). Order yang dibuat bulan lalu tapi dibayar bulan ini terhitung di bulan ini.

---

## 6. Poin Pelanggan Tidak Bertambah

### Gejala
Order selesai tapi poin pelanggan tidak bertambah.

### Penyebab & Solusi

| Penyebab | Solusi |
|----------|--------|
| Status belum mencapai `selesai` | Poin hanya ditambah satu kali saat status = `selesai` |
| Pelanggan tidak terhubung ke order | Cek apakah field `pelanggan_id` di order terisi |
| Poin order = 0 | Terjadi jika `pembayaran.total = 0` (order gratis, atau data pembayaran null) |

**Cek manual:** Buka detail pelanggan → lihat tab Poin. Tambah poin manual jika diperlukan via **Reward → Kelola Poin**.

---

## 7. Voucher Tidak Bisa Dipakai

### Gejala
Kode voucher dimasukkan tapi tidak memberikan diskon atau muncul pesan error.

### Checklist Voucher Valid

- [ ] Kode benar (huruf kapital, tanpa spasi)
- [ ] Voucher aktif (toggle aktif = ON)
- [ ] Tanggal saat ini ≤ `expired_at` (atau `expired_at` kosong)
- [ ] `terpakai` < `kuota` (atau `kuota` kosong = unlimited)
- [ ] Total order ≥ `min_transaksi`

**Jika semua terpenuhi tapi tetap tidak bisa:** Cek log aplikasi untuk error validasi.

---

## 8. Export Excel / PDF Error atau File Tidak Terunduh

### Gejala
Klik export tidak mengunduh file atau muncul error.

### Penyebab & Solusi

| Penyebab | Solusi |
|----------|--------|
| Library tidak terinstal | Jalankan `composer install` di direktori proyek |
| PHP extension kurang | Pastikan `ext-zip`, `ext-gd` aktif di `php.ini` |
| Memory PHP kurang | Naikkan `memory_limit` di `php.ini` ke 256M atau lebih |
| Pop-up blocker browser | Izinkan pop-up / unduhan dari domain ini di browser |

---

## 9. Stok Tidak Akurat Setelah Mutasi

### Gejala
Stok menunjukkan angka yang tidak sesuai ekspektasi.

### Penyebab & Solusi

- **Mutasi `keluar` terlalu besar:** Sistem membatasi minimum 0 (`max(0, stok - jumlah)`). Tidak bisa negatif.
- **Mutasi ganda:** Cek riwayat mutasi di `/stok/{id}/riwayat` untuk melihat semua perubahan.
- **Race condition (akses bersamaan):** Sistem menggunakan `lockForUpdate()` database lock untuk mencegah ini, tapi kemungkinan kecil tetap bisa terjadi di beban tinggi.
- **Koreksi:** Gunakan tipe mutasi `penyesuaian` untuk mengeset stok ke nilai yang benar.

---

## 10. Error 403 Forbidden

### Gejala
Halaman menampilkan "403 Akses tidak diizinkan" atau "Halaman ini hanya untuk owner".

### Penyebab & Solusi

| Pesan Error | Penyebab | Solusi |
|------------|----------|--------|
| "Halaman ini hanya untuk owner" | Mengakses menu yang hanya untuk Owner (Karyawan, HPP master) | Login sebagai Owner atau minta Owner untuk menyelesaikan |
| "Akses tidak diizinkan" | Tidak punya permission yang diperlukan | Hubungi Owner untuk menambahkan permission di menu Karyawan → Hak Akses |

---

## 11. Halaman Dashboard Lambat

### Gejala
Dashboard loading lama, terutama bagian grafik atau top items.

### Solusi

1. **Tambahkan index database:** Jalankan migrasi terbaru (`php artisan migrate`).
2. **Cek query berat:** Untuk codebase besar, pertimbangkan caching dashboard menggunakan `php artisan cache:clear` lalu tambah caching di `DashboardController`.
3. **Optimalkan server:** Pastikan PHP OPcache aktif di production.

---

## 12. Nomor HP Format Salah

### Gejala
WhatsApp tidak bisa terkirim atau format nomor error.

### Aturan Format Nomor HP

Sistem menerima format:
- `08123456789` → dikonversi ke `628123456789`
- `628123456789` → tetap
- `+628123456789` → spasi/strip/plus dihapus, dikonversi ke `628123456789`

Nomor dengan format lain mungkin tidak terkirim. Pastikan nomor HP diisi dengan format Indonesia yang valid.

---

## 13. Data Pelanggan Ganda

### Gejala
Pelanggan yang sama muncul dua kali dengan nomor HP berbeda (misal: `08123` dan `628123`).

### Penjelasan

Pelanggan diidentifikasi **unique berdasarkan nomor HP** persis seperti yang diinput. Jika pernah diinput dengan format berbeda, dua record terpisah akan terbuat.

### Solusi

Saat ini tidak ada fitur merge pelanggan. Solusi manual: update nomor HP salah satu ke format yang sama via **Edit Pelanggan**, atau koordinasi via database langsung (Perlu Verifikasi Manual).

---

## 14. Template WA Tidak Berubah Setelah Diedit

### Gejala
Pesan WA yang dikirim masih menggunakan teks lama meskipun template sudah diedit.

### Penyebab & Solusi

Template WA di-cache (atau bisa jadi ada cache di level PHP/server). Coba:
1. Jalankan `php artisan cache:clear`
2. Pastikan perubahan tersimpan (muncul pesan "Template WA berhasil disimpan")
3. Kirim order test baru untuk memverifikasi template baru berjalan

---

## 15. Fitur Tidak Tersedia / Tombol Tidak Muncul

### Gejala
Tombol atau menu tertentu tidak terlihat meskipun seharusnya ada.

### Penjelasan

Akses dikontrol berdasarkan permission role. Cek dengan Owner:
- Apakah permission yang sesuai sudah diberikan ke role Anda?
- Beberapa fitur hanya untuk **Owner**: Karyawan management, HPP master, toggle aktif owner lain.
- Beberapa fitur hanya untuk **Admin atau Owner**: Lihat Reviews.

---

*Masalah tidak ditemukan di sini? Lihat log di `storage/logs/laravel.log` atau hubungi developer.*

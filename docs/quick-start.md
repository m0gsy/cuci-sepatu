# Quick Start — Step Shine Works

**Panduan 5 Menit untuk Admin Baru**

Ikuti langkah ini untuk memproses order pertama Anda dari awal hingga selesai.

---

## Langkah 1: Login

1. Buka browser, akses URL aplikasi (contoh: `http://localhost:8000`).
2. Masukkan **Email** dan **Password** yang diberikan oleh Owner.
3. Klik **Login**.

Jika Anda lupa password, klik **"Lupa Password?"** dan ikuti instruksi di email.

---

## Langkah 2: Setup Layanan (Pertama Kali Saja)

> Lewati langkah ini jika layanan sudah tersedia.

Sistem sudah menyertakan 5 layanan default (Cuci Biasa, Deep Clean, Premium, Repaint, Cuci Sandal). Jika perlu menambah layanan:

1. Klik menu **Layanan** di sidebar.
2. Klik tombol **Tambah Layanan**.
3. Isi nama, harga, dan estimasi hari.
4. Klik **Simpan**.

---

## Langkah 3: Input Order Pertama

1. Klik menu **Order** → klik tombol **Buat Order Baru**.
2. Isi form order:

   | Field | Contoh |
   |-------|--------|
   | Nama Pelanggan | Budi Santoso |
   | No. HP | 08123456789 |
   | Layanan | Cuci Biasa |
   | Jenis Sepatu | Running |
   | Jumlah Pasang | 1 |
   | Estimasi Selesai | (pilih tanggal) |
   | Metode Bayar | `cash` (langsung lunas) atau `tempo` (bayar nanti) |

3. Klik **Simpan Order**.

**Hasil yang terjadi secara otomatis:**
- Nomor order dibuat: contoh `ORD-20260703-0001`
- Data pelanggan disimpan (atau diperbarui jika HP sudah terdaftar)
- Notifikasi WhatsApp dikirim ke pelanggan jika kredensial Twilio dikonfigurasi.
- Status order: **Diterima**

---

## Langkah 4: Update Status Order

Saat sepatu mulai diproses, update statusnya:

1. Di halaman daftar order atau halaman detail order, cari tombol **Update Status**.
2. Klik untuk mengubah status sesuai tahap:

   ```
   Diterima → Inspeksi → Dicuci → Pengeringan → Finishing → Siap Diambil → Diambil
   ```

3. Saat status berubah ke **Dicuci**: WA otomatis dikirim ke pelanggan.
4. Saat status berubah ke **Siap Diambil**: WA otomatis dikirim, poin pelanggan ditambah.

---

## Langkah 5: Tandai Lunas

Jika metode bayar `tempo` atau `transfer` (belum lunas saat input):

1. Buka halaman detail order.
2. Klik tombol **Tandai Lunas**.
3. Konfirmasi.

Jika sudah menggunakan metode `cash`, `lunas`, atau `qris` saat input order → pembayaran sudah otomatis lunas.

---

## Langkah 6: Cetak Nota (Opsional)

1. Buka halaman detail order.
2. Klik **Cetak Nota**.
3. Browser membuka PDF nota termal (80mm) — cetak atau simpan.

---

## Langkah 7: Selesaikan Order

Saat sepatu sudah diambil pelanggan, ubah status dari **Siap Diambil** ke **Selesai**.

Status ini secara otomatis menandai pembayaran sebagai lunas jika belum lunas.

---

## Ringkasan Alur

```
Login → Buat Order → Diproses → Siap Diambil → Selesai
```

---

## Tips Cepat

- **Cari order:** Gunakan kotak pencarian di halaman daftar order — bisa cari nama, nomor HP, atau nomor order.
- **Pelanggan baru otomatis:** Tidak perlu daftarkan pelanggan dulu — cukup masukkan nomor HP di form order, sistem akan membuat data pelanggan baru.
- **Voucher:** Masukkan kode voucher di form order sebelum simpan untuk menerapkan diskon.
- **Dashboard:** Cek dashboard setiap hari untuk melihat order terlambat, stok menipis, dan pendapatan hari ini.
- **WA tidak terkirim:** Periksa `TWILIO_SID`, `TWILIO_AUTH_TOKEN`, dan `TWILIO_WHATSAPP_FROM` di `.env`, lalu pastikan queue worker aktif.

---

*Butuh bantuan lebih? Lihat [Panduan Lengkap](admin-user-guide.md) atau [FAQ](faq.md).*

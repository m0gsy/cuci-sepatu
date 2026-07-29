# Business Flow - Step Shine Works

Dokumen ini menggambarkan alur yang dijalankan aplikasi saat ini.

## 1. Order

```mermaid
flowchart TD
    A[Admin mengisi order] --> B[Validasi pelanggan, item, lokasi, voucher, dan poin]
    B --> C{Idempotency key sudah ada?}
    C -->|Ya| D[Kembalikan order yang sama]
    C -->|Tidak| E[Transaksi database]
    E --> F[Kunci pelanggan, voucher, dan stok terkait]
    F --> G[Simpan order, item, pembayaran, dan diskon]
    G --> H[Potong stok dan catat mutasi aktual]
    H --> I[Commit]
    I --> J[Antrekan WA order_masuk]
```

Pembuatan order bersifat atomik. Kegagalan pada salah satu penulisan akan
melakukan rollback seluruh transaksi. `idempotency_key` mencegah order ganda
ketika form dikirim ulang.

Status hanya dapat bergerak maju:

```mermaid
stateDiagram-v2
    [*] --> draft
    draft --> menunggu_pembayaran
    draft --> diproses
    menunggu_pembayaran --> diproses
    diproses --> siap_diambil
    siap_diambil --> selesai
    draft --> batal
    menunggu_pembayaran --> batal
```

- Masuk `diproses`: antrekan WA `mulai_dicuci`.
- Masuk `siap_diambil`: set waktu siap dan antrekan WA `order_selesai`.
- Masuk `selesai`: pembayaran yang belum lunas ditandai lunas dan poin diberikan satu kali.
- Masuk `batal`: stok, poin yang ditukar, dan pemakaian voucher dikembalikan satu kali.
- Order `siap_diambil`, `selesai`, atau `batal` tidak dapat diedit.

## 2. Pembayaran dan Laporan

```mermaid
flowchart LR
    A[Order] --> B{cash, qris, atau lunas?}
    B -->|Ya| C[Lunas dan dibayar_pada diisi]
    B -->|Tidak| D[Belum lunas]
    D --> E[Tandai lunas manual atau order selesai]
    E --> C
    C --> F[Laporan menurut dibayar_pada]
```

Hanya pembayaran berstatus `selesai` yang masuk laporan. Order batal
dikecualikan. Net sales membagi diskon voucher dan diskon poin secara
proporsional ke setiap item agar rekap layanan dan lokasi tetap konsisten.

## 3. Harga, Voucher, dan Poin

Harga item memakai urutan berikut:

1. Override harga layanan pada lokasi.
2. Harga standar layanan ditambah biaya lokasi bila lokasi memakai harga custom.
3. Harga standar layanan.

Voucher divalidasi berdasarkan status aktif, periode berlaku, minimum transaksi,
dan batas pemakaian. Penukaran poin bernilai Rp100 per poin dan tidak boleh
melebihi saldo maupun nilai transaksi setelah voucher.

Poin order dihitung `floor(total pembayaran / 10.000)` dan baru diberikan saat
status `selesai`. Setiap award atau refund memiliki idempotency key agar retry
tidak menggandakan saldo.

## 4. Stok

```mermaid
flowchart TD
    A[Order dibuat] --> B[Ambil resep setiap layanan]
    B --> C[Kunci baris stok]
    C --> D[Kurangi sebanyak stok yang tersedia]
    D --> E[Catat mutasi keluar dengan order_id]
    E --> F{Order dibatalkan?}
    F -->|Tidak| G[Selesai]
    F -->|Ya| H[Kembalikan jumlah mutasi aktual]
    H --> I[Catat mutasi masuk dengan reversed_mutation_id]
```

Jika stok tidak cukup, sistem memotong jumlah yang tersedia dan menampilkan
peringatan kepada operator. Pembatalan hanya mengembalikan jumlah yang benar-benar
pernah dipotong, bukan menghitung ulang resep.

## 5. WhatsApp

Pesan transaksional dikirim melalui job queue dan Twilio WhatsApp API:

| Pemicu | Template |
| --- | --- |
| Order dibuat | `order_masuk` |
| Status menjadi `diproses` | `mulai_dicuci` |
| Status menjadi `siap_diambil` | `order_selesai` |
| Operator mengirim invoice | `invoice` |

Nomor dinormalisasi ke format internasional Indonesia. Kredensial Twilio yang
tidak lengkap membuat pengiriman dilewati dan dicatat sebagai warning. Kegagalan
provider di-retry maksimal tiga kali dengan jeda 60 detik.

Pesan `siap_diambil` hanya menampilkan estimasi poin. Saldo poin bertambah setelah
order menjadi `selesai`.

## 6. Tracking dan Review Publik

Setiap order memiliki token publik acak. Pelanggan dapat melihat status tanpa
login. Form review baru tersedia untuk order `selesai`; satu order hanya dapat
memiliki satu review dengan rating 1-5 dan ulasan maksimal 500 karakter.

---

Sumber implementasi utama:
`app/Http/Controllers/OrderController.php`,
`app/Models/Order.php`,
`app/Services/StockAutomationService.php`,
`app/Services/WhatsappService.php`, dan
`app/Jobs/KirimWaJob.php`.

Terakhir diperbarui: 29 Juli 2026.

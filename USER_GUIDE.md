    # User Guide — Step Shine Works

    ## Akun & Login

    Buka browser → `http://localhost:8000` → masuk dengan email dan password.

    | Role | Akses default |
    |---|---|
    | **Owner** | Semua fitur |
    | **Admin** | Order, pelanggan, lokasi |
    | **Cleaner** | Lihat order, update status, upload foto |

    > Owner bisa ubah hak akses Admin dan Cleaner lewat menu **Karyawan → Hak akses per role**.

    ---

    ## Owner

    ### Dashboard
    Tampilan utama berisi:
    - **Sales Summary** bulan ini — Gross Sales, Net Sales, Gross Profit, jumlah transaksi, rata-rata, dan margin
    - **Hari ini** — pendapatan, profit, order dalam proses, siap diambil
    - **Grafik 7 hari** — bar chart pendapatan harian
    - **Top Items** — layanan terlaris bulan ini
    - **Order terbaru** — 10 order terakhir

    ### Laporan
    Menu **Laporan** → filter periode → lihat rekap penjualan. Bisa export **Excel** atau **PDF**.

    ### Profit / Loss & HPP
    - **Profit / Loss** — laporan gross profit per periode
    - **Kelola HPP** → input biaya bahan baku per layanan (sabun, sikat, dll.)

    ### Master Layanan & Harga
    Menu **Master layanan** → tambah/edit nama layanan, harga dasar, aktif/nonaktif.

    Harga khusus per lokasi: buka **Lokasi sepatu** → pilih lokasi → **Atur harga**.

    ### Voucher
    Menu **Voucher** → buat kode voucher dengan nominal/persen diskon, batas penggunaan, dan masa berlaku.

    ### Reward & Poin
    Menu **Reward & Poin** → atur berapa rupiah per 1 poin, minimum penukaran, dan daftar reward.
    Tambah poin manual ke pelanggan via tab **Kelola Poin**.

    ### Stok Bahan
    Menu **Stok bahan** → catat stok bahan (sabun, sikat, dll.), input mutasi keluar/masuk, lihat riwayat.

    ### Operasional
    Menu **Operasional** → catat biaya operasional harian (listrik, sewa, gaji, dll.) per kategori.

    ### Karyawan
    Menu **Karyawan** → kelola akun staff:
    - **Tambah karyawan** — nama, email, role, password
    - **Edit** — ubah nama, email, role
    - **Nonaktifkan** — akun tidak bisa login tapi data tetap ada
    - **Reset password** — ganti password staff
    - **Hak akses per role** — centang/uncentang menu yang bisa diakses Admin dan Cleaner

    ---

    ## Admin

    ### Buat Order Baru
    **Order baru** → isi form:
    - Nama pelanggan & no. HP (atau pilih dari daftar pelanggan)
    - Layanan, jenis sepatu, merek, warna, kondisi
    - Jumlah pasang
    - Lokasi sepatu (rak/loker)
    - Estimasi selesai
    - Metode bayar

    Klik **Simpan** → WA notifikasi konfirmasi order otomatis terkirim ke customer.

    ### Daftar Order
    - Filter by status atau cari nama/no. order/no. HP
    - Klik no. order untuk buka detail

    ### Detail Order
    Di halaman detail order tersedia:
    - **Edit order** — ubah detail (hanya jika belum selesai)
    - **Tandai status** — update progress pengerjaan
    - **Upload foto** — foto before/during/after
    - **Kirim invoice WA** — kirim rincian tagihan ke customer
    - **Kirim ulang WA** — kirim ulang notifikasi status
    - **Cetak nota** — buka PDF nota untuk dicetak

    ### Update Status Order
    Urutan status:

    ```
    Diterima → Inspeksi → Dicuci → Kering → Finishing → Siap diambil → Selesai
    ```

    - Saat status **Dicuci** → WA notifikasi "mulai diproses" otomatis terkirim
    - Saat status **Siap diambil** → WA notifikasi "selesai, silakan ambil" otomatis terkirim + poin customer ditambah

    ### Pelanggan
    Menu **Pelanggan** → lihat daftar, total belanja, poin, riwayat order.
    Klik **Riwayat →** untuk lihat semua order pelanggan tersebut.

    ### Lokasi Sepatu
    Menu **Lokasi sepatu** → lihat daftar lokasi/rak. Assign lokasi ke order lewat tombol di detail order.

    ### Ulasan
    Menu **Ulasan** → lihat semua ulasan yang diberikan customer (rating bintang + komentar).

    ---

    ## Cleaner

    ### Melihat Pekerjaan
    Menu **Pekerjaan saya** → lihat daftar semua order aktif.

    ### Update Status / Progress
    Buka detail order → **Tandai status** → pilih status sesuai progress pengerjaan.

    ### Upload Foto
    Di detail order → bagian **Foto** → pilih tipe foto:
    - **Before** — kondisi sebelum dicuci
    - **During** — proses pencucian
    - **After** — hasil setelah selesai

    Pilih file foto → **Upload**.

    ---

    ## Customer (Tanpa Login)

    Customer tidak perlu punya akun. Akses via link WA yang dikirim otomatis saat order dibuat.

    ### Pantau Status Order
    Klik link di WA → halaman status terbuka di browser HP → lihat progress real-time.

    ### Berikan Ulasan
    Setelah order **siap diambil** → muncul form ulasan di halaman status:
    1. Pilih bintang (1–5)
    2. Tulis komentar (opsional)
    3. Klik **Kirim ulasan**

    ---

    ## Notifikasi WhatsApp Otomatis

    | Trigger | Kapan | Isi |
    |---|---|---|
    | Order dibuat | Saat admin simpan order baru | Konfirmasi order, detail, estimasi, link tracking |
    | Mulai dicuci | Status diubah ke *Dicuci* | Info pengerjaan dimulai + link tracking |
    | Siap diambil | Status diubah ke *Siap diambil* | Notif selesai, total tagihan, poin earned + link tracking |
    | Invoice | Manual (tombol di detail order) | Rincian tagihan lengkap + link nota PDF |

    > Pastikan akun Twilio Anda memiliki saldo yang cukup agar WA bisa terkirim.

    ---

    ## Pengaturan Twilio (WhatsApp Gateway)

    1. Login ke Twilio Console.
    2. Dapatkan `Account SID` dan `Auth Token` dari dashboard.
    3. Hubungkan nomor pengirim WhatsApp Anda (atau gunakan Sandbox Twilio).
    4. Isi konfigurasi di file `.env`:
       - `TWILIO_SID=xxxxx`
       - `TWILIO_AUTH_TOKEN=xxxxx`
       - `TWILIO_WHATSAPP_FROM=+14155238886`

# FAQ — Step Shine Works

Pertanyaan yang sering diajukan oleh administrator sistem.

---

## Autentikasi & Akun

**1. Saya tidak bisa login. Apa yang harus saya lakukan?**

Pastikan email dan password benar (case-sensitive). Jika tetap tidak bisa, kemungkinan:
- Akun dinonaktifkan oleh Owner → hubungi Owner untuk mengaktifkan kembali.
- Password salah → gunakan fitur "Lupa Password?" di halaman login.
- Email belum terverifikasi → periksa kotak masuk email dan klik link verifikasi.

**2. Bagaimana cara reset password?**

Buka `/forgot-password`, masukkan email, lalu ikuti link yang dikirim ke email. Link reset berlaku terbatas waktu.

**3. Bisakah saya mendaftar sendiri sebagai karyawan baru?**

Tidak. Registrasi mandiri dinonaktifkan. Hanya Owner yang bisa membuat akun karyawan baru melalui menu **Karyawan**.

**4. Bagaimana cara mengubah password saya sendiri?**

Login → klik nama di header → **Profil** → isi field password baru dan konfirmasi → simpan.

**5. Apakah akun saya bisa dinonaktifkan tanpa sepengetahuan saya?**

Ya, Owner bisa menonaktifkan akun karyawan kapan saja. Jika Anda tiba-tiba tidak bisa login padahal password benar, hubungi Owner.

---

## Order

**6. Bagaimana format nomor order?**

Format: `ORD-YYYYMMDD-XXXX`. Contoh: `ORD-20260703-0001`. Nomor urut di-reset setiap hari.

**7. Bisakah dua order punya nomor yang sama?**

Tidak. Sistem menggunakan database lock untuk mencegah duplikasi nomor order pada input bersamaan.

**8. Apakah pelanggan harus didaftarkan dulu sebelum membuat order?**

Tidak. Cukup masukkan nama dan nomor HP di form order. Jika nomor HP belum ada di database, sistem otomatis membuat data pelanggan baru.

**9. Apa yang terjadi jika nomor HP pelanggan sudah terdaftar dengan nama berbeda?**

Sistem menggunakan nomor HP sebagai identifikasi unik pelanggan. Jika nama berbeda, nama di data pelanggan diperbarui sesuai input order terbaru.

**10. Saya salah memasukkan data order. Bisakah diedit?**

Ya, selama status order belum `siap_diambil`, `selesai`, atau `diambil`. Buka detail order → klik **Edit Order**.

**11. Bisakah saya mengubah order yang sudah selesai?**

Tidak. Order dengan status `siap_diambil`, `selesai`, atau `diambil` tidak bisa diedit untuk menjaga integritas data.

**12. Bagaimana cara membatalkan order?**

Tidak ada fitur batal order di sistem. Untuk order yang perlu dibatalkan, Anda bisa mengubah status ke `selesai` dan tandai sebagai lunas dengan nominal Rp 0 (Perlu Verifikasi Manual — tidak ada fitur khusus cancel di codebase).

**13. Apakah bisa input order untuk lebih dari 1 pasang sekaligus?**

Ya. Isi field **Jumlah Pasang** (maksimal 20). Total dihitung otomatis: harga × jumlah pasang.

**14. Apa perbedaan metode bayar `lunas`, `cash`, dan `qris`?**

Ketiganya langsung menandai pembayaran sebagai lunas saat order dibuat. Perbedaannya hanya pada label/catatan metode pembayaran. `tempo` dan `transfer` harus ditandai lunas secara manual nanti.

**15. Bagaimana cara mengetahui mana order yang belum lunas?**

Filter daftar order berdasarkan status, atau lihat kolom status pembayaran di tabel order. Halaman detail order juga menampilkan status pembayaran dengan jelas.

**16. Apakah status bisa diubah mundur (contoh: dari `dicuci` kembali ke `inspeksi`)?**

Ya, sistem tidak membatasi perubahan status ke arah mundur. Namun trigger otomatis (kirim WA, tambah poin) hanya terjadi satu arah.

**17. Mengapa ada status `antri`, `proses`, `diambil` di sistem?**

Ini adalah status lama (legacy) untuk order yang diinput sebelum sistem diperbarui ke alur 7 tahap. Order baru menggunakan status `diterima` s.d. `selesai`.

**18. Kapan `selesai_pada` diisi?**

Otomatis diisi saat status pertama kali berubah ke `siap_diambil`. Tidak berubah jika status diubah balik.

---

## Pembayaran

**19. Bagaimana cara tandai order sebagai lunas?**

Buka detail order → klik tombol **Tandai Lunas**. Sistem mengisi `dibayar_pada` dengan waktu sekarang.

**20. Bisakah saya ubah metode bayar setelah order dibuat?**

Tidak ada fitur khusus untuk ubah metode bayar setelah order dibuat (Perlu Verifikasi Manual). Gunakan fitur Edit Order jika tersedia, namun field metode bayar tidak termasuk dalam form edit.

**21. Apa yang terjadi pada pembayaran saat status berubah ke `selesai`?**

Jika pembayaran belum lunas, sistem otomatis menandainya lunas saat status berubah ke `selesai`.

---

## Voucher

**22. Bagaimana cara menerapkan voucher ke order?**

Masukkan kode voucher di field **Kode Voucher** pada form order baru. Sistem otomatis memvalidasi dan menghitung diskon.

**23. Mengapa voucher saya tidak bisa dipakai?**

Cek kondisi berikut:
- Voucher nonaktif (toggle aktif/nonaktif)
- Tanggal expired sudah lewat
- Kuota sudah habis (`terpakai >= kuota`)
- Total order di bawah minimal transaksi

**24. Bisakah voucher diubah kodenya setelah dibuat?**

Tidak. Kode voucher tidak bisa diubah setelah dibuat. Buat voucher baru jika diperlukan kode berbeda.

**25. Bisakah voucher yang sudah dipakai dihapus?**

Tidak. Voucher yang sudah digunakan di minimal 1 order tidak bisa dihapus untuk menjaga integritas data historis.

**26. Apakah satu order bisa menggunakan lebih dari satu voucher?**

Tidak. Satu order hanya bisa menggunakan satu voucher.

---

## WhatsApp Notifikasi

**27. WA tidak terkirim ke pelanggan. Apa penyebabnya?**

Kemungkinan penyebab:
- `WABLAS_TOKEN` belum diisi di file `.env`
- Token Wablas tidak valid atau sudah expired
- Nomor HP pelanggan tidak valid atau tidak terdaftar di WhatsApp
- Server Wablas sedang down
- Lihat log di `storage/logs/laravel.log` untuk detail error

**28. Kapan saja WA dikirim otomatis?**

| Trigger | Template |
|---------|---------|
| Order baru dibuat | `order_masuk` |
| Status berubah ke `dicuci` | `mulai_dicuci` |
| Status berubah ke `siap_diambil` | `order_selesai` |

**29. Bagaimana cara kirim ulang WA yang gagal?**

Buka detail order → klik **Kirim Ulang WA**.

**30. Apakah WA dikirim secara langsung atau antrian?**

Antrian (async queue). Jika queue tidak berjalan (tidak ada worker), pengiriman akan tertunda. Jalankan `php artisan queue:work` untuk memproses antrian.

**31. Berapa kali sistem mencoba kirim WA jika gagal?**

3 kali dengan jeda 60 detik. Jika semua percobaan gagal, error dicatat permanen di log.

**32. Bisakah saya mengubah teks pesan WhatsApp?**

Ya. Menu **Template WA** → pilih template → edit isi pesan → simpan. Gunakan `{variabel}` yang tersedia.

---

## Pelanggan & Poin

**33. Bagaimana sistem menghitung poin pelanggan?**

1 poin per Rp 10.000 yang dibayar. Contoh: order total Rp 50.000 → pelanggan mendapat 5 poin. Poin ditambahkan saat status berubah ke `siap_diambil`.

**34. Kapan tier pelanggan diperbarui?**

Otomatis setiap kali status order berubah ke `siap_diambil`. Tier dihitung berdasarkan akumulasi total belanja lunas.

**35. Bisakah saya mengurangi poin pelanggan?**

Ya, melalui **Reward & Poin → Kelola Poin**. Pilih pelanggan, tipe `kurang`, masukkan jumlah dan keterangan.

**36. Bagaimana cara menukar poin dengan reward?**

Fitur penukaran poin untuk pelanggan adalah proses manual saat ini. Admin mengurangi poin via **Kelola Poin** dan memberikan reward secara offline (Perlu Verifikasi Manual — tidak ada flow penukaran otomatis di codebase).

**37. Bisakah pelanggan yang sama punya dua akun?**

Tidak. Pelanggan diidentifikasi berdasarkan nomor HP (unik). Satu nomor HP = satu pelanggan.

---

## Layanan & Lokasi

**38. Mengapa layanan saya tidak muncul di form order?**

Pastikan layanan dalam status **Aktif**. Layanan nonaktif tidak ditampilkan di form order.

**39. Apakah mengubah harga layanan memengaruhi order lama?**

Tidak. Setiap order menyimpan `harga_satuan` sendiri saat dibuat. Order lama tidak terpengaruh perubahan harga layanan.

**40. Apa bedanya "Harga Tambahan" dengan "Harga Per Layanan" di lokasi?**

- **Harga Tambahan (Global):** Semua layanan di lokasi ini dikenakan harga layanan + harga_tambahan.
- **Override Per Layanan:** Harga spesifik untuk satu layanan tertentu di lokasi ini (lebih prioritas dari harga tambahan).

---

## HPP & Laporan

**41. Apa itu HPP dan mengapa penting?**

HPP (Harga Pokok Produksi) adalah total biaya bahan/proses untuk mengerjakan satu order layanan. Digunakan untuk menghitung gross profit dan gross margin.

**42. Siapa yang bisa melihat dan mengubah HPP?**

- **Melihat laporan HPP:** Pengguna dengan permission `hpp`.
- **Mengubah master HPP (komponen):** Hanya **Owner**.

**43. Bagaimana HPP dihitung per order?**

HPP per order = total semua komponen HPP layanan × jumlah pasang. Dihitung otomatis saat order dibuat.

**44. Bisakah saya override HPP manual untuk satu order tertentu?**

Ya. Di form order baru, ada field **HPP Override**. Isi nilai untuk menggantikan kalkulasi otomatis.

**45. Kenapa gross sales di laporan berbeda dengan net sales?**

Gross sales = harga × jumlah pasang (sebelum diskon). Net sales = actual yang dibayar (setelah diskon voucher). Selisihnya adalah total diskon.

**46. Laporan tidak menampilkan order tertentu. Mengapa?**

Laporan hanya mencakup order dengan status aktif (bukan `antri` saja). Order dengan status `diterima` ke atas sudah termasuk. Pastikan filter bulan sudah benar.

**47. Format apa yang tersedia untuk export laporan?**

PDF (A4 portrait) dan Excel (.xlsx dengan 2 sheet: Rekap Bulanan dan Detail Order).

---

## Stok Bahan

**48. Bagaimana cara menambah stok bahan yang habis?**

Buka menu **Stok** → klik bahan yang bersangkutan → klik **Mutasi** → pilih tipe `masuk` → masukkan jumlah dan keterangan.

**49. Apa bedanya mutasi `keluar` dengan `penyesuaian`?**

- `keluar`: Mengurangi stok sejumlah yang diinput. Tidak bisa minus (minimum 0).
- `penyesuaian`: Mengubah stok langsung ke nilai yang diinput (bukan penambahan/pengurangan).

**50. Alert stok menipis muncul di mana?**

Di halaman **Dashboard** (bagian bawah) dan halaman daftar **Stok** dengan badge berwarna amber/merah.

---

## Sistem & Teknis

**51. Apakah ada fitur backup data?**

Tidak ada fitur backup bawaan di aplikasi. Backup harus dilakukan di level server/database (Perlu Verifikasi Manual).

**52. Bagaimana cara mengakses log error sistem?**

Log tersimpan di `storage/logs/laravel.log`. Hanya bisa diakses via server/SSH, bukan dari dalam aplikasi.

**53. Apakah aplikasi bisa diakses dari smartphone?**

Ya, tampilan responsif menggunakan Tailwind CSS. Bisa diakses dari browser smartphone.

**54. Mengapa halaman load sangat lambat?**

Kemungkinan penyebab:
- Queue worker tidak berjalan (WA job menumpuk)
- Database belum terindeks (jalankan migrasi terbaru)
- Server resource tidak mencukupi

**55. Bagaimana cara menjalankan queue worker untuk WA?**

```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

Untuk production, gunakan Supervisor atau layanan process manager.

---

*Pertanyaan lain? Lihat [Panduan Lengkap](admin-user-guide.md) atau [Troubleshooting](troubleshooting.md).*

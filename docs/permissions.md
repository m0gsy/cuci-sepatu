# Matriks Permission — Step Shine Works

Dokumen ini menjelaskan sistem hak akses lengkap di Step Shine Works.

---

## Ringkasan Peran (Role)

Sistem memiliki **3 peran** yang didefinisikan di `app/Models/User.php`:

| Peran | Deskripsi |
|-------|-----------|
| `owner` | Akses penuh ke semua fitur. Tidak bisa dibatasi. |
| `admin` | Akses dikonfigurasi oleh Owner via tabel `role_permissions`. |
| `cleaner` | Akses dikonfigurasi oleh Owner via tabel `role_permissions`. |

**Catatan penting:**
- Permission berlaku per **role**, bukan per **user**. Semua user dengan role `admin` memiliki hak yang sama.
- Owner selalu memiliki akses penuh (`hasPermission()` mengembalikan `true` untuk semua permission).
- Permission di-cache selama 5 menit di Redis/file cache.

---

## Matriks Permission Lengkap

Keterangan kolom:
- **Owner**: Selalu bisa (hardcoded)
- **Admin**: Bergantung konfigurasi Owner (bisa diaktifkan/nonaktifkan)
- **Cleaner**: Bergantung konfigurasi Owner (bisa diaktifkan/nonaktifkan)

### A. Fitur dengan Permission Dinamis (Bisa Dikonfigurasi)

| Fitur | Key Permission | Owner | Admin | Cleaner |
|-------|---------------|-------|-------|---------|
| Input / edit order & pembayaran | `orders.manage` | ✅ | Dikonfigurasi | Dikonfigurasi |
| Lihat daftar order (index) | *(tidak dibatasi)* | ✅ | ✅ | ✅ |
| Data pelanggan (CRUD) | `pelanggan` | ✅ | Dikonfigurasi | Dikonfigurasi |
| Lokasi sepatu (CRUD + harga) | `lokasi` | ✅ | Dikonfigurasi | Dikonfigurasi |
| Laporan penjualan | `laporan` | ✅ | Dikonfigurasi | Dikonfigurasi |
| Profit / Loss & Laporan HPP | `hpp` | ✅ | Dikonfigurasi | Dikonfigurasi |
| Master layanan & harga | `layanans` | ✅ | Dikonfigurasi | Dikonfigurasi |
| Voucher (CRUD) | `vouchers` | ✅ | Dikonfigurasi | Dikonfigurasi |
| Reward & Poin (CRUD + kelola poin) | `rewards` | ✅ | Dikonfigurasi | Dikonfigurasi |
| Stok bahan (CRUD + mutasi) | `stok` | ✅ | Dikonfigurasi | Dikonfigurasi |
| Operasional (biaya) | `operasional` | ✅ | Dikonfigurasi | Dikonfigurasi |
| Template WhatsApp | `wa_template` | ✅ | Dikonfigurasi* | Dikonfigurasi* |

> *`wa_template` tidak muncul di UI manajemen permission Karyawan saat ini — perlu dikelola via database langsung jika ingin memberikan akses ke admin/cleaner (Perlu Verifikasi Manual).

### B. Fitur Hanya untuk Owner (Hardcoded `owner` Middleware)

| Fitur | URL | Owner | Admin | Cleaner |
|-------|-----|-------|-------|---------|
| Master HPP — tambah/edit/hapus komponen | `/hpp`, `POST/PUT/DELETE /hpp` | ✅ | ❌ | ❌ |
| Manajemen Karyawan — lihat daftar | `/karyawans` | ✅ | ❌ | ❌ |
| Manajemen Karyawan — tambah akun | `POST /karyawans` | ✅ | ❌ | ❌ |
| Manajemen Karyawan — detail | `/karyawans/{id}` | ✅ | ❌ | ❌ |
| Manajemen Karyawan — edit data | `PUT /karyawans/{id}` | ✅ | ❌ | ❌ |
| Toggle aktif/nonaktif karyawan | `PATCH /karyawans/{id}/toggle-aktif` | ✅ | ❌ | ❌ |
| Reset password karyawan | `PATCH /karyawans/{id}/reset-password` | ✅ | ❌ | ❌ |
| Simpan konfigurasi permission | `POST /karyawans/permissions` | ✅ | ❌ | ❌ |

### C. Fitur Hanya untuk Admin atau Owner (`admin-or-owner` Middleware)

| Fitur | URL | Owner | Admin | Cleaner |
|-------|-----|-------|-------|---------|
| Lihat ulasan / review pelanggan | `/reviews` | ✅ | ✅ | ❌ |

> Catatan: `isAdmin()` di `User.php` mengembalikan `true` untuk role `owner` maupun `admin`.

### D. Fitur Publik (Tanpa Autentikasi)

| Fitur | URL | Semua |
|-------|-----|-------|
| Beranda | `/` | ✅ |
| Tentang | `/about` | ✅ |
| Kontak | `/contact` | ✅ |
| Kebijakan Privasi | `/privacy-policy` | ✅ |
| Syarat & Ketentuan | `/terms` | ✅ |
| Kebijakan Refund | `/refund-policy` | ✅ |
| Tracking status order (via token) | `/status/{token}` | ✅ |
| Submit review pelanggan (via token) | `POST /orders/{token}/review` | ✅ |

### E. Fitur yang Dapat Diakses Semua User Terautentikasi

| Fitur | URL | Owner | Admin | Cleaner |
|-------|-----|-------|-------|---------|
| Dashboard | `/dashboard` | ✅ | ✅ | ✅* |
| Profil (edit sendiri) | `/profil` | ✅ | ✅ | ✅ |
| Lihat daftar order | `/orders` | ✅ | ✅ | ✅ |

> *Cleaner melihat versi dashboard yang berbeda (hanya daftar order aktif, tanpa data finansial).

---

## Cara Mengatur Permission

### Via UI (untuk role `admin` dan `cleaner`)

1. Login sebagai **Owner**.
2. Buka menu **Karyawan** → `/karyawans`.
3. Scroll ke bagian **Hak Akses** atau klik tombol yang sesuai.
4. Centang permission yang ingin diberikan ke role Admin dan/atau Cleaner.
5. Klik **Simpan Hak Akses**.

Perubahan berlaku setelah cache expired (5 menit) atau sesi login baru.

### Via Database (untuk troubleshooting)

Tabel `role_permissions` dengan kolom `role` dan `permission`:
```sql
-- Berikan permission orders.manage ke admin
INSERT INTO role_permissions (role, permission) VALUES ('admin', 'orders.manage');

-- Hapus semua permission cleaner
DELETE FROM role_permissions WHERE role = 'cleaner';
```

---

## Contoh Skenario Konfigurasi Umum

### Skenario 1: Admin Kasir

Admin yang hanya bertugas input order dan tandai lunas:

| Permission | Aktif? |
|-----------|--------|
| `orders.manage` | ✅ |
| `pelanggan` | ✅ |
| `vouchers` | ✅ |
| `laporan` | ❌ |
| `hpp` | ❌ |
| `layanans` | ❌ |
| `lokasi` | ❌ |
| `stok` | ❌ |
| `operasional` | ❌ |
| `rewards` | ❌ |

### Skenario 2: Cleaner (Teknisi Cuci)

Cleaner yang hanya perlu update status order:

| Permission | Aktif? |
|-----------|--------|
| `orders.manage` | ✅ (untuk update status) |
| semua lainnya | ❌ |

### Skenario 3: Admin Manajer

Admin yang mengelola semua operasional kecuali keuangan sensitif:

| Permission | Aktif? |
|-----------|--------|
| `orders.manage` | ✅ |
| `pelanggan` | ✅ |
| `lokasi` | ✅ |
| `layanans` | ✅ |
| `vouchers` | ✅ |
| `rewards` | ✅ |
| `stok` | ✅ |
| `operasional` | ✅ |
| `laporan` | ✅ |
| `hpp` | ❌ (khusus Owner) |

---

## Catatan Teknis

- **Middleware `permission`:** `CheckPermission.php` — memeriksa `user->hasPermission($key)`.
- **Middleware `owner`:** `OwnerOnly.php` — memeriksa `user->isOwner()`.
- **Middleware `admin-or-owner`:** `AdminOrOwner.php` — memeriksa `user->isAdmin()` (true untuk admin & owner).
- **Cache key:** `role_perms_{role}` — TTL 300 detik (5 menit).
- **Bust cache:** Otomatis setelah Owner menyimpan perubahan permission. Manual: `php artisan cache:clear`.

---

*Terakhir diperbarui: 3 Juli 2026*

# Fitur Manajemen Karyawan

## File yang perlu dicopy ke project Laravel

### 1. Migration
Jalankan dulu:
```
php artisan migrate
```
File: database/migrations/2025_01_02_000001_add_karyawan_fields_to_users_table.php

### 2. Models
- app/Models/User.php → TIMPA file yang sudah ada

### 3. Middleware
- app/Http/Middleware/AdminOnly.php → file BARU

### 4. Controllers
- app/Http/Controllers/KaryawanController.php → file BARU
- app/Http/Controllers/Auth/AuthenticatedSessionController.php → TIMPA file yang sudah ada

### 5. Routes
- routes/web.php → TIMPA file yang sudah ada (sudah include semua route sebelumnya)

### 6. bootstrap/app.php
- bootstrap/app.php → TIMPA file yang sudah ada (untuk daftarkan middleware 'admin')

### 7. Views
- resources/views/layouts/app.blade.php → TIMPA (ada menu Karyawan untuk admin)
- resources/views/karyawans/index.blade.php → file BARU
- resources/views/karyawans/show.blade.php → file BARU

### 8. Seeder (opsional)
- database/seeders/DatabaseSeeder.php → TIMPA (sudah include akun operator contoh)
  Jalankan: php artisan db:seed

## Login default
- Admin  : admin@cucisepatu.com  / password
- Operator: andi@cucisepatu.com  / password

## Fitur
- Daftar karyawan + statistik order hari ini & bulan ini
- Role admin (ungu) vs operator (biru)
- Edit data, reset password, aktifkan/nonaktifkan inline
- Halaman detail: statistik, grafik 6 bulan, semua order
- Operator yang dinonaktifkan tidak bisa login
- Menu Karyawan hanya tampil untuk admin
- Minimal 1 admin aktif harus ada

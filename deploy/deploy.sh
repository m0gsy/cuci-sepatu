#!/bin/bash
# Deploy script untuk Step Shine Works
# Jalankan dari root project: bash deploy/deploy.sh

set -e   # stop jika ada error

echo "=== Step Shine Works — Deploy ==="

# 1. Pull kode terbaru
git pull origin main

# 2. Install/update dependencies (tanpa dev packages)
composer install --no-dev --optimize-autoloader

# 3. Build frontend assets
npm ci
npm run build

# 4. Jalankan migrasi
php artisan migrate --force

# 5. Buat symbolic link storage (foto upload)
php artisan storage:link 2>/dev/null || echo "Storage link sudah ada."

# 6. Clear semua cache lama dulu
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 7. Buat cache baru (lebih cepat dari file cache biasa)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Restart queue worker agar pakai kode terbaru
sudo supervisorctl restart step-shine-queue:*

echo ""
echo "=== Deploy selesai! ==="
echo "Cek status queue: sudo supervisorctl status"

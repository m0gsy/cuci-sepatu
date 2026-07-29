<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        } // MODIFY COLUMN not supported in SQLite
        // 1. Perluas ENUM dulu agar bisa tampung semua nilai sementara
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','operator','cleaner','owner') NOT NULL DEFAULT 'admin'");
        // 2. Rename data: admin → owner, operator → admin
        DB::statement("UPDATE users SET role = 'owner' WHERE role = 'admin'");
        DB::statement("UPDATE users SET role = 'admin' WHERE role = 'operator'");
        // 3. Tutup ENUM ke nilai final
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner','admin','cleaner') NOT NULL DEFAULT 'admin'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner','admin','cleaner','operator') NOT NULL DEFAULT 'admin'");
        DB::statement("UPDATE users SET role = 'operator' WHERE role = 'admin'");
        DB::statement("UPDATE users SET role = 'admin' WHERE role = 'owner'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','operator','cleaner') NOT NULL DEFAULT 'operator'");
    }
};

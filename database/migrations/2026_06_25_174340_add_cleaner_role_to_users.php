<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') return;
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','operator','cleaner') NOT NULL DEFAULT 'operator'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') return;
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','operator') NOT NULL DEFAULT 'operator'");
    }
};

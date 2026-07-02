<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['admin', 'cleaner']);
            $table->string('permission', 50);
            $table->unique(['role', 'permission']);
            $table->timestamps();
        });

        // Default: admin dapat akses orders, pelanggan, lokasi
        DB::table('role_permissions')->insert([
            ['role' => 'admin', 'permission' => 'orders.manage', 'created_at' => now(), 'updated_at' => now()],
            ['role' => 'admin', 'permission' => 'pelanggan',     'created_at' => now(), 'updated_at' => now()],
            ['role' => 'admin', 'permission' => 'lokasi',        'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};

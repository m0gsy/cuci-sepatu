<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\RolePermission;

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
        $defaults = [
            ['role' => 'admin', 'permission' => 'orders.manage'],
            ['role' => 'admin', 'permission' => 'pelanggan'],
            ['role' => 'admin', 'permission' => 'lokasi'],
        ];
        foreach ($defaults as $d) {
            RolePermission::create($d);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'operator'])->default('operator')->after('email');
            $table->string('no_hp', 20)->nullable()->after('role');
            $table->string('alamat')->nullable()->after('no_hp');
            $table->boolean('aktif')->default(true)->after('alamat');
            $table->timestamp('last_login')->nullable()->after('aktif');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'no_hp', 'alamat', 'aktif', 'last_login']);
        });
    }
};

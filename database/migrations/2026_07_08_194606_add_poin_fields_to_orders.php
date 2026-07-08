<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // poin yang digunakan untuk diskon (jumlah poin, bukan rupiah)
            $table->unsignedInteger('poin_digunakan')->default(0)->after('diskon');
            // nilai rupiah dari poin yang digunakan (poin_digunakan * 100)
            $table->unsignedInteger('diskon_poin')->default(0)->after('poin_digunakan');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['poin_digunakan', 'diskon_poin']);
        });
    }
};

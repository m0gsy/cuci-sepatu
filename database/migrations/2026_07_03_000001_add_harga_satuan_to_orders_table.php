<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('harga_satuan')->nullable()->after('jumlah_pasang');
        });

        // Backfill dari pembayaran.total + diskon untuk order yang sudah ada
        DB::statement('
            UPDATE orders o
            LEFT JOIN pembayarans p ON p.order_id = o.id
            SET o.harga_satuan = ROUND((COALESCE(p.total, 0) + o.diskon) / o.jumlah_pasang)
            WHERE o.harga_satuan IS NULL AND o.jumlah_pasang > 0
        ');
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('harga_satuan');
        });
    }
};

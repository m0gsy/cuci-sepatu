<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // order_items.jenis_barang_id — used in report JOIN queries
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('jenis_barang_id', 'order_items_jenis_barang_idx');
        });

        // orders.pelanggan_id — used on customer history page
        Schema::table('orders', function (Blueprint $table) {
            $table->index('pelanggan_id', 'orders_pelanggan_idx');
            $table->index('no_order', 'orders_no_order_idx');
        });

        // stok_mutasis.stok_id — used on stok riwayat page
        if (Schema::hasTable('stok_mutasis')) {
            Schema::table('stok_mutasis', function (Blueprint $table) {
                $table->index('stok_id', 'stok_mutasis_stok_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_jenis_barang_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_pelanggan_idx');
            $table->dropIndex('orders_no_order_idx');
        });

        if (Schema::hasTable('stok_mutasis')) {
            Schema::table('stok_mutasis', function (Blueprint $table) {
                $table->dropIndex('stok_mutasis_stok_idx');
            });
        }
    }
};

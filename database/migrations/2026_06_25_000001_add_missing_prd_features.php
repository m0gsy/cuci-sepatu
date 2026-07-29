<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Voucher ───────────────────────────────────────────────────────────
        if (! Schema::hasTable('vouchers')) {
            Schema::create('vouchers', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 30)->unique();
                $table->enum('tipe', ['persen', 'nominal']);
                $table->integer('nilai');
                $table->date('expired_at')->nullable();
                $table->integer('min_transaksi')->default(0);
                $table->integer('kuota')->nullable();
                $table->integer('terpakai')->default(0);
                $table->boolean('aktif')->default(true);
                $table->text('deskripsi')->nullable();
                $table->timestamps();
            });
        }

        // ── Foto order (before / during / after) ──────────────────────────────
        // ── Review / rating pelanggan ─────────────────────────────────────────
        if (! Schema::hasTable('foto_orders')) {
            Schema::create('foto_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->enum('tipe', ['before', 'during', 'after']);
                $table->string('path');
                $table->string('keterangan', 100)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
                $table->foreignId('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
                $table->tinyInteger('rating');
                $table->text('ulasan')->nullable();
                $table->timestamps();
            });
        }

        // ── Orders: kolom detail sepatu + voucher ─────────────────────────────
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'merek')) {
                $table->string('merek', 50)->nullable()->after('jenis_sepatu');
            }
            if (! Schema::hasColumn('orders', 'warna')) {
                $table->string('warna', 30)->nullable()->after('merek');
            }
            if (! Schema::hasColumn('orders', 'kondisi')) {
                $table->string('kondisi', 100)->nullable()->after('warna');
            }
            if (! Schema::hasColumn('orders', 'voucher_id')) {
                $table->foreignId('voucher_id')->nullable()
                    ->after('catatan_lokasi')
                    ->constrained('vouchers')->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'diskon')) {
                $table->integer('diskon')->default(0)->after('voucher_id');
            }
        });

        // ── Pelanggans: tier membership ───────────────────────────────────────
        if (! Schema::hasColumn('pelanggans', 'tier')) {
            Schema::table('pelanggans', function (Blueprint $table) {
                $table->string('tier', 20)->default('reguler')->after('poin');
            });
        }

        // ── Enum MySQL: status order + metode bayar cash/qris ─────────
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'antri','proses','diambil',
                'diterima','inspeksi','dicuci','kering','finishing','siap_diambil','selesai'
            ) NOT NULL DEFAULT 'diterima'");

            DB::statement("ALTER TABLE pembayarans MODIFY COLUMN metode ENUM(
                'tempo','transfer','lunas','cash','qris'
            ) NOT NULL DEFAULT 'tempo'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('foto_orders');

        Schema::table('orders', function (Blueprint $table) {
            foreach (['merek', 'warna', 'kondisi', 'diskon'] as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('orders', 'voucher_id')) {
                $table->dropForeign(['voucher_id']);
                $table->dropColumn('voucher_id');
            }
        });

        if (Schema::hasColumn('pelanggans', 'tier')) {
            Schema::table('pelanggans', fn ($t) => $t->dropColumn('tier'));
        }

        Schema::dropIfExists('vouchers');
    }
};

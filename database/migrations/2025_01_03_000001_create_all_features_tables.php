<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Pelanggan ────────────────────────────────────────────────────
        if (! Schema::hasTable('pelanggans')) {
            Schema::create('pelanggans', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('no_hp', 20)->unique();
                $table->string('alamat')->nullable();
                $table->text('catatan')->nullable();
                $table->integer('poin')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('orders', 'pelanggan_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('pelanggan_id')->nullable()->after('user_id')
                    ->constrained('pelanggans')->nullOnDelete();
            });
        }

        // Token publik untuk cek status order
        if (! Schema::hasColumn('orders', 'token_publik')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('token_publik', 32)->nullable()->unique()->after('no_order');
            });
        }

        // ── Stok bahan baku ──────────────────────────────────────────────
        if (! Schema::hasTable('stoks')) {
            Schema::create('stoks', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('satuan', 30)->default('pcs');
                $table->decimal('stok_saat_ini', 10, 2)->default(0);
                $table->decimal('stok_minimum', 10, 2)->default(5);
                $table->integer('harga_satuan')->default(0);
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('stok_mutasis')) {
            Schema::create('stok_mutasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stok_id')->constrained('stoks')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->enum('tipe', ['masuk', 'keluar', 'penyesuaian']);
                $table->decimal('jumlah', 10, 2);
                $table->decimal('stok_sebelum', 10, 2);
                $table->decimal('stok_sesudah', 10, 2);
                $table->string('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // ── Biaya operasional ────────────────────────────────────────────
        if (! Schema::hasTable('operasionals')) {
            Schema::create('operasionals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('nama');
                $table->enum('kategori', ['bahan', 'utilitas', 'gaji', 'sewa', 'peralatan', 'lainnya']);
                $table->integer('jumlah');
                $table->date('tanggal');
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        // ── Program loyalitas ────────────────────────────────────────────
        if (! Schema::hasTable('poin_histories')) {
            Schema::create('poin_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->enum('tipe', ['tambah', 'tukar']);
                $table->integer('poin');
                $table->string('keterangan');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('rewards')) {
            Schema::create('rewards', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->integer('poin_dibutuhkan');
                $table->text('deskripsi')->nullable();
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('poin_histories');
        Schema::dropIfExists('rewards');
        Schema::dropIfExists('stok_mutasis');
        Schema::dropIfExists('stoks');
        Schema::dropIfExists('operasionals');

        if (Schema::hasColumn('orders', 'pelanggan_id')) {
            Schema::table('orders', fn ($t) => $t->dropForeign(['pelanggan_id']));
            Schema::table('orders', fn ($t) => $t->dropColumn('pelanggan_id'));
        }
        if (Schema::hasColumn('orders', 'token_publik')) {
            Schema::table('orders', fn ($t) => $t->dropColumn('token_publik'));
        }

        Schema::dropIfExists('pelanggans');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel lokasi — sekarang punya harga sendiri
        if (! Schema::hasTable('lokasis')) {
            Schema::create('lokasis', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 20)->unique();
                $table->string('nama');
                $table->text('deskripsi')->nullable();
                $table->boolean('harga_custom')->default(false); // apakah lokasi punya harga sendiri
                $table->integer('harga_tambahan')->default(0);   // tambahan harga jika lokasi khusus
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'lokasi_id')) {
                $table->foreignId('lokasi_id')->nullable()->after('pelanggan_id')
                    ->constrained('lokasis')->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'hpp')) {
                $table->integer('hpp')->default(0)->after('lokasi_id');
            }
            if (! Schema::hasColumn('orders', 'catatan_lokasi')) {
                $table->string('catatan_lokasi')->nullable()->after('hpp');
            }
        });

        if (! Schema::hasTable('hpp_layanans')) {
            Schema::create('hpp_layanans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('layanan_id')->constrained('layanans')->cascadeOnDelete();
                $table->string('komponen');
                $table->integer('biaya');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'lokasi_id')) {
                $table->dropForeign(['lokasi_id']);
                $table->dropColumn('lokasi_id');
            }
            if (Schema::hasColumn('orders', 'hpp')) {
                $table->dropColumn('hpp');
            }
            if (Schema::hasColumn('orders', 'catatan_lokasi')) {
                $table->dropColumn('catatan_lokasi');
            }
        });
        Schema::dropIfExists('hpp_layanans');
        Schema::dropIfExists('lokasis');
    }
};

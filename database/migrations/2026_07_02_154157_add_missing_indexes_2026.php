<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->index('nama');
        });

        Schema::table('operasionals', function (Blueprint $table) {
            $table->index('tanggal');
        });

        Schema::table('hpp_layanans', function (Blueprint $table) {
            $table->unique(['layanan_id', 'komponen'], 'hpp_layanans_layanan_komponen_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pelanggans', fn (Blueprint $t) => $t->dropIndex(['nama']));
        Schema::table('operasionals', fn (Blueprint $t) => $t->dropIndex(['tanggal']));
        Schema::table('hpp_layanans', fn (Blueprint $t) => $t->dropUnique('hpp_layanans_layanan_komponen_unique'));
    }
};

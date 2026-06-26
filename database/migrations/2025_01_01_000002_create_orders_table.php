<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('no_order')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama_pelanggan');
            $table->string('no_hp', 20);
            $table->foreignId('layanan_id')->constrained('layanans');
            $table->string('jenis_sepatu', 50);
            $table->integer('jumlah_pasang')->default(1);
            $table->text('catatan')->nullable();
            $table->enum('status', ['antri', 'proses', 'selesai', 'diambil'])->default('antri');
            $table->date('estimasi_selesai');
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

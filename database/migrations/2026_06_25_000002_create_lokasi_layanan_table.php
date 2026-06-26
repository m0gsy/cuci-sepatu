<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lokasi_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lokasi_id')->constrained('lokasis')->cascadeOnDelete();
            $table->foreignId('layanan_id')->constrained('layanans')->cascadeOnDelete();
            $table->integer('harga');
            $table->timestamps();
            $table->unique(['lokasi_id', 'layanan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lokasi_layanan');
    }
};

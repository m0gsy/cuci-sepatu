<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Kategori Layanan
        Schema::create('kategori_layanans', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // 2. Jenis Barang (previously hardcoded Jenis Sepatu)
        Schema::create('jenis_barangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // 3. Bahan Master (Daftar Bahan Baku)
        Schema::create('bahans', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('satuan', 30)->default('pcs');
            $table->integer('harga_beli')->default(0);
            $table->integer('isi_kemasan')->default(1);
            $table->decimal('harga_satuan', 12, 2)->default(0.00);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // 4. Layanan Recipes (Kelola Bahan Baku / Recipe HPP)
        Schema::create('layanan_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layanan_id')->constrained('layanans')->cascadeOnDelete();
            $table->foreignId('bahan_id')->constrained('bahans')->cascadeOnDelete();
            $table->decimal('jumlah_penggunaan', 10, 2)->default(1.00);
            $table->timestamps();
        });

        // 5. Order Items (Order Multiple Item)
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('layanan_id')->constrained('layanans')->cascadeOnDelete();
            $table->foreignId('jenis_barang_id')->constrained('jenis_barangs')->cascadeOnDelete();
            $table->integer('jumlah_pasang')->default(1);
            $table->integer('harga_satuan')->default(0);
            $table->string('merek', 50)->nullable();
            $table->string('warna', 30)->nullable();
            $table->string('kondisi', 100)->nullable();
            $table->integer('hpp')->default(0);
            $table->integer('gross_profit')->default(0);
            $table->decimal('gross_margin', 6, 2)->default(0.00);
            $table->timestamps();
        });

        // 6. Alter Layanans Table
        Schema::table('layanans', function (Blueprint $table) {
            $table->foreignId('kategori_layanan_id')->nullable()->after('id')->constrained('kategori_layanans')->nullOnDelete();
            $table->integer('estimasi_nilai')->default(2)->after('harga');
            $table->string('estimasi_satuan', 20)->default('Hari')->after('estimasi_nilai');
        });

        // 7. Alter Stoks Table
        Schema::table('stoks', function (Blueprint $table) {
            $table->foreignId('bahan_id')->nullable()->after('id')->constrained('bahans')->cascadeOnDelete();
            $table->string('nama', 100)->nullable()->change();
            $table->string('satuan', 30)->nullable()->change();
            $table->integer('harga_satuan')->nullable()->change();
        });

        // 8. Alter Orders Table
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 30)->default('diproses')->change();
            $table->datetime('estimasi_selesai')->change();
            $table->foreignId('layanan_id')->nullable()->change();
            $table->string('jenis_sepatu', 50)->nullable()->change();
            $table->integer('jumlah_pasang')->nullable()->change();
            $table->integer('harga_satuan')->nullable()->change();
            $table->integer('hpp')->nullable()->change();
        });

        // 8.5. Alter Pembayarans Table
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('status', 30)->default('belum_selesai')->change();
        });

        // 9. Backfill Data
        // 9.1. Create default Kategori
        $kategoriId = DB::table('kategori_layanans')->insertGetId([
            'nama' => 'Umum',
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Map existing layanans to this category
        DB::table('layanans')->update([
            'kategori_layanan_id' => $kategoriId,
            'estimasi_nilai' => DB::raw('estimasi_hari'),
            'estimasi_satuan' => 'Hari',
        ]);

        // 9.2. Create default Jenis Barang
        $jenisDefault = ['Sneakers', 'Running', 'Boots', 'Formal', 'Sandal', 'Lainnya'];
        $jenisMap = [];
        foreach ($jenisDefault as $item) {
            $id = DB::table('jenis_barangs')->insertGetId([
                'nama' => $item,
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $jenisMap[strtolower($item)] = $id;
        }

        // 9.3. Map existing stoks to bahans
        $stoks = DB::table('stoks')->get();
        foreach ($stoks as $stok) {
            $bahanId = DB::table('bahans')->insertGetId([
                'nama' => $stok->nama,
                'satuan' => $stok->satuan,
                'harga_beli' => $stok->harga_satuan,
                'isi_kemasan' => 1,
                'harga_satuan' => $stok->harga_satuan,
                'aktif' => true,
                'created_at' => $stok->created_at,
                'updated_at' => $stok->updated_at,
            ]);
            DB::table('stoks')->where('id', $stok->id)->update(['bahan_id' => $bahanId]);
        }

        // 9.4. Migrate manual HPP to recipes
        $hppLayanans = DB::table('hpp_layanans')->get();
        foreach ($hppLayanans as $hpp) {
            // Find or create bahan matching component name
            $bahan = DB::table('bahans')->where('nama', $hpp->komponen)->first();
            if ($bahan) {
                $bahanId = $bahan->id;
            } else {
                $bahanId = DB::table('bahans')->insertGetId([
                    'nama' => $hpp->komponen,
                    'satuan' => 'pcs',
                    'harga_beli' => $hpp->biaya,
                    'isi_kemasan' => 1,
                    'harga_satuan' => $hpp->biaya,
                    'aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('layanan_recipes')->insert([
                'layanan_id' => $hpp->layanan_id,
                'bahan_id' => $bahanId,
                'jumlah_penggunaan' => 1.00,
                'created_at' => $hpp->created_at,
                'updated_at' => $hpp->updated_at,
            ]);
        }

        // 9.5. Migrate existing orders to order_items
        $orders = DB::table('orders')->get();
        foreach ($orders as $order) {
            // Map jenis_sepatu to jenis_barang_id
            $js = strtolower($order->jenis_sepatu);
            $jbId = $jenisMap[$js] ?? $jenisMap['lainnya'] ?? 1;

            // Calculate gross profit and margin
            $grossProfit = ($order->harga_satuan * $order->jumlah_pasang) - $order->hpp;
            $grossMargin = 0.00;
            if ($order->harga_satuan * $order->jumlah_pasang > 0) {
                $grossMargin = round(($grossProfit / ($order->harga_satuan * $order->jumlah_pasang)) * 100, 2);
            }

            DB::table('order_items')->insert([
                'order_id' => $order->id,
                'layanan_id' => $order->layanan_id,
                'jenis_barang_id' => $jbId,
                'jumlah_pasang' => $order->jumlah_pasang,
                'harga_satuan' => $order->harga_satuan ?? 0,
                'merek' => $order->merek,
                'warna' => $order->warna,
                'kondisi' => $order->kondisi,
                'hpp' => $order->hpp,
                'gross_profit' => $grossProfit,
                'gross_margin' => $grossMargin,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]);
        }

        // 9.6. Migrate existing statuses to new statuses
        // New: draft, menunggu_pembayaran, diproses, siap_diambil, selesai, batal
        // Old: antri, proses, diambil, diterima, inspeksi, dicuci, kering, finishing, siap_diambil, selesai
        DB::table('orders')->whereIn('status', ['antri'])->update(['status' => 'menunggu_pembayaran']);
        DB::table('orders')->whereIn('status', ['proses', 'diterima', 'inspeksi', 'dicuci', 'kering', 'finishing'])->update(['status' => 'diproses']);
        DB::table('orders')->whereIn('status', ['siap_diambil'])->update(['status' => 'siap_diambil']);
        DB::table('orders')->whereIn('status', ['selesai', 'diambil'])->update(['status' => 'selesai']);

        // 9.7. Map pembayaran status
        // Old: belum, dp, lunas
        // New: belum_selesai, selesai
        DB::table('pembayarans')->whereIn('status', ['belum', 'dp'])->update(['status' => 'belum_selesai']);
        DB::table('pembayarans')->where('status', 'lunas')->update(['status' => 'selesai']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status mappings
        DB::table('pembayarans')->where('status', 'belum_selesai')->update(['status' => 'belum']);
        DB::table('pembayarans')->where('status', 'selesai')->update(['status' => 'lunas']);

        DB::table('orders')->where('status', 'menunggu_pembayaran')->update(['status' => 'antri']);
        DB::table('orders')->where('status', 'diproses')->update(['status' => 'proses']);

        Schema::table('orders', function (Blueprint $table) {
            $table->date('estimasi_selesai')->change();
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('status', 30)->default('belum')->change();
        });

        Schema::table('stoks', function (Blueprint $table) {
            $table->string('nama', 100)->nullable(false)->change();
            $table->string('satuan', 30)->nullable(false)->change();
            $table->integer('harga_satuan')->nullable(false)->change();
            $table->dropForeign(['bahan_id']);
            $table->dropColumn('bahan_id');
        });

        Schema::table('layanans', function (Blueprint $table) {
            $table->dropForeign(['kategori_layanan_id']);
            $table->dropColumn(['kategori_layanan_id', 'estimasi_nilai', 'estimasi_satuan']);
        });

        Schema::dropIfExists('order_items');
        Schema::dropIfExists('layanan_recipes');
        Schema::dropIfExists('bahans');
        Schema::dropIfExists('jenis_barangs');
        Schema::dropIfExists('kategori_layanans');
    }
};

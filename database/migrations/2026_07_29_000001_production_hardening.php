<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ponytail: additive schema first and each step guarded, so a failed data
        // check below can never leave the app missing columns it reads at runtime,
        // and a partially-applied run can simply be re-migrated.
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'idempotency_key')) {
                $table->uuid('idempotency_key')->nullable()->unique()->after('token_publik');
            }
            if (! Schema::hasColumn('orders', 'poin_diberikan_pada')) {
                $table->timestamp('poin_diberikan_pada')->nullable()->after('diskon_poin');
            }
            if (! Schema::hasColumn('orders', 'voucher_dikembalikan_pada')) {
                $table->timestamp('voucher_dikembalikan_pada')->nullable()->after('poin_diberikan_pada');
            }
        });

        Schema::table('stok_mutasis', function (Blueprint $table) {
            if (! Schema::hasColumn('stok_mutasis', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('stok_id')
                    ->constrained('orders')->nullOnDelete();
            }
            if (! Schema::hasColumn('stok_mutasis', 'reversed_mutation_id')) {
                $table->foreignId('reversed_mutation_id')->nullable()->unique()->after('order_id')
                    ->constrained('stok_mutasis')->nullOnDelete();
            }
        });

        Schema::table('poin_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('poin_histories', 'event_key')) {
                $table->string('event_key', 100)->nullable()->unique()->after('order_id');
            }
        });

        if (! Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('email', 150);
                $table->string('subject', 200)->nullable();
                $table->text('message');
                $table->timestamps();
            });
        }

        Schema::dropIfExists('foto_orders');

        // Data-integrity dependent work. Throws on dirty data; clean it up and re-run.
        $this->assertUniqueData('pembayarans', ['order_id']);
        $this->assertUniqueData('stoks', ['bahan_id']);
        $this->assertUniqueData('layanan_recipes', ['layanan_id', 'bahan_id']);

        if (DB::table('stoks')->whereNull('bahan_id')->exists()) {
            throw new RuntimeException('Migration blocked: stoks.bahan_id contains NULL. Link every stock row to a bahan before retrying.');
        }

        if (! Schema::hasIndex('pembayarans', 'pembayarans_order_id_unique')) {
            Schema::table('pembayarans', fn (Blueprint $table) => $table->unique('order_id'));
        }

        Schema::table('stoks', fn (Blueprint $table) => $table->foreignId('bahan_id')->nullable(false)->change());
        if (! Schema::hasIndex('stoks', 'stoks_bahan_id_unique')) {
            Schema::table('stoks', fn (Blueprint $table) => $table->unique('bahan_id'));
        }

        if (! Schema::hasIndex('layanan_recipes', 'layanan_recipes_layanan_id_bahan_id_unique')) {
            Schema::table('layanan_recipes', fn (Blueprint $table) => $table->unique(['layanan_id', 'bahan_id']));
        }

        if (Schema::hasIndex('pembayarans', 'pembayarans_order_id_rollback_idx')) {
            Schema::table('pembayarans', fn (Blueprint $table) => $table->dropIndex('pembayarans_order_id_rollback_idx'));
        }
        if (Schema::hasIndex('stoks', 'stoks_bahan_id_rollback_idx')) {
            Schema::table('stoks', fn (Blueprint $table) => $table->dropIndex('stoks_bahan_id_rollback_idx'));
        }
        if (Schema::hasIndex('layanan_recipes', 'layanan_recipes_layanan_id_rollback_idx')) {
            Schema::table('layanan_recipes', fn (Blueprint $table) => $table->dropIndex('layanan_recipes_layanan_id_rollback_idx'));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::create('foto_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('tipe', ['before', 'during', 'after']);
            $table->string('path');
            $table->string('keterangan', 100)->nullable();
            $table->timestamps();
        });

        Schema::table('poin_histories', function (Blueprint $table) {
            $table->dropUnique(['event_key']);
            $table->dropColumn('event_key');
        });

        Schema::table('stok_mutasis', function (Blueprint $table) {
            $table->dropForeign(['reversed_mutation_id']);
            $table->dropUnique(['reversed_mutation_id']);
            $table->dropForeign(['order_id']);
            $table->dropColumn(['order_id', 'reversed_mutation_id']);
        });

        Schema::table('layanan_recipes', function (Blueprint $table) {
            $table->index('layanan_id', 'layanan_recipes_layanan_id_rollback_idx');
            $table->dropUnique(['layanan_id', 'bahan_id']);
        });
        Schema::table('stoks', function (Blueprint $table) {
            $table->index('bahan_id', 'stoks_bahan_id_rollback_idx');
            $table->dropUnique(['bahan_id']);
            $table->foreignId('bahan_id')->nullable()->change();
        });
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->index('order_id', 'pembayarans_order_id_rollback_idx');
            $table->dropUnique(['order_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn(['idempotency_key', 'poin_diberikan_pada', 'voucher_dikembalikan_pada']);
        });
    }

    private function assertUniqueData(string $table, array $columns): void
    {
        $hasDuplicates = DB::table($table)
            ->select($columns)
            ->whereNotNull($columns[0])
            ->groupBy($columns)
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicates) {
            throw new RuntimeException("Migration blocked: duplicate {$table} rows violate unique key ".implode(', ', $columns).'.');
        }
    }
};

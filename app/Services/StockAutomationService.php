<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Stok;
use App\Models\StokMutasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAutomationService
{
    public function deductStock(Order $order): array
    {
        $warnings = [];

        DB::transaction(function () use ($order, &$warnings) {
            $order->loadMissing('items.layanan.recipes.bahan.stok');

            foreach ($order->items as $item) {
                $layanan = $item->layanan;
                if (! $layanan) {
                    continue;
                }

                foreach ($layanan->recipes as $recipe) {
                    $bahan = $recipe->bahan;
                    if (! $bahan) {
                        continue;
                    }

                    Stok::firstOrCreate(
                        ['bahan_id' => $bahan->id],
                        ['stok_saat_ini' => 0, 'stok_minimum' => 0]
                    );
                    $stok = Stok::where('bahan_id', $bahan->id)->lockForUpdate()->firstOrFail();

                    $dibutuhkan = (float) $recipe->jumlah_penggunaan * (int) $item->jumlah_pasang;
                    if ($dibutuhkan <= 0) {
                        continue;
                    }

                    $sebelum = (float) $stok->stok_saat_ini;
                    $dikurangi = min($sebelum, $dibutuhkan);
                    if ($dikurangi < $dibutuhkan) {
                        $warnings[] = "Stok bahan baku '{$bahan->nama}' tidak mencukupi (tersedia: {$sebelum} {$bahan->satuan}, dibutuhkan: {$dibutuhkan} {$bahan->satuan}).";
                    }

                    if ($dikurangi <= 0) {
                        continue;
                    }

                    $sesudah = $sebelum - $dikurangi;
                    $stok->update(['stok_saat_ini' => $sesudah]);

                    StokMutasi::create([
                        'stok_id' => $stok->id,
                        'order_id' => $order->id,
                        'user_id' => Auth::id() ?? $order->user_id,
                        'tipe' => 'keluar',
                        'jumlah' => $dikurangi,
                        'stok_sebelum' => $sebelum,
                        'stok_sesudah' => $sesudah,
                        'keterangan' => "Potong otomatis order {$order->no_order}",
                    ]);
                }
            }
        });

        return $warnings;
    }

    public function reverseStock(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $mutations = StokMutasi::where('order_id', $order->id)
                ->where('tipe', 'keluar')
                ->whereDoesntHave('reversal')
                ->lockForUpdate()
                ->get();

            foreach ($mutations as $mutation) {
                $stok = Stok::lockForUpdate()->findOrFail($mutation->stok_id);
                $sebelum = (float) $stok->stok_saat_ini;
                $sesudah = $sebelum + (float) $mutation->jumlah;

                $stok->update(['stok_saat_ini' => $sesudah]);
                StokMutasi::create([
                    'stok_id' => $stok->id,
                    'order_id' => $order->id,
                    'reversed_mutation_id' => $mutation->id,
                    'user_id' => Auth::id() ?? $order->user_id,
                    'tipe' => 'masuk',
                    'jumlah' => $mutation->jumlah,
                    'stok_sebelum' => $sebelum,
                    'stok_sesudah' => $sesudah,
                    'keterangan' => "Pengembalian stok pembatalan order {$order->no_order}",
                ]);
            }
        });
    }
}

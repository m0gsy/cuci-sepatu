<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Stok;
use App\Models\StokMutasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockAutomationService
{
    /**
     * Deduct stock for an order and return warnings if stock is insufficient.
     *
     * @param Order $order
     * @return array Array of warning messages
     */
    public function deductStock(Order $order): array
    {
        $warnings = [];

        DB::transaction(function () use ($order, &$warnings) {
            foreach ($order->items as $item) {
                $layanan = $item->layanan;
                if (!$layanan) continue;

                // Load recipes
                $recipes = $layanan->recipes;
                foreach ($recipes as $recipe) {
                    $bahan = $recipe->bahan;
                    if (!$bahan) continue;

                    $stok = $bahan->stok;
                    if (!$stok) {
                        // Auto create stok if missing
                        $stok = Stok::create([
                            'bahan_id'      => $bahan->id,
                            'stok_saat_ini' => 0.00,
                            'stok_minimum'  => 0.00,
                        ]);
                    }

                    // Lock stock record
                    $stok = Stok::lockForUpdate()->find($stok->id);

                    $deduction = (float)$recipe->jumlah_penggunaan * (int)$item->jumlah_pasang;
                    if ($deduction <= 0) continue;

                    $sebelum = $stok->stok_saat_ini;
                    if ($sebelum < $deduction) {
                        $warnings[] = "Stok bahan baku '{$bahan->nama}' tidak mencukupi untuk item sepatu ini (Stok saat ini: {$sebelum} {$bahan->satuan}, dibutuhkan: {$deduction} {$bahan->satuan}).";
                    }

                    $sesudah = max(0.00, $sebelum - $deduction);
                    $stok->update(['stok_saat_ini' => $sesudah]);

                    StokMutasi::create([
                        'stok_id'      => $stok->id,
                        'user_id'      => Auth::id() ?? $order->user_id,
                        'tipe'         => 'keluar',
                        'jumlah'       => $deduction,
                        'stok_sebelum' => $sebelum,
                        'stok_sesudah' => $sesudah,
                        'keterangan'   => "Potong otomatis order {$order->no_order}",
                    ]);
                }
            }
        });

        return $warnings;
    }

    /**
     * Reverse stock deduction when order is cancelled.
     *
     * @param Order $order
     * @return void
     */
    public function reverseStock(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $layanan = $item->layanan;
                if (!$layanan) continue;

                $recipes = $layanan->recipes;
                foreach ($recipes as $recipe) {
                    $bahan = $recipe->bahan;
                    if (!$bahan) continue;

                    $stok = $bahan->stok;
                    if (!$stok) continue;

                    // Lock stock record
                    $stok = Stok::lockForUpdate()->find($stok->id);

                    $addition = (float)$recipe->jumlah_penggunaan * (int)$item->jumlah_pasang;
                    if ($addition <= 0) continue;

                    $sebelum = $stok->stok_saat_ini;
                    $sesudah = $sebelum + $addition;

                    $stok->update(['stok_saat_ini' => $sesudah]);

                    StokMutasi::create([
                        'stok_id'      => $stok->id,
                        'user_id'      => Auth::id() ?? $order->user_id,
                        'tipe'         => 'masuk',
                        'jumlah'       => $addition,
                        'stok_sebelum' => $sebelum,
                        'stok_sesudah' => $sesudah,
                        'keterangan'   => "Pengembalian stok pembatalan order {$order->no_order}",
                    ]);
                }
            }
        });
    }
}

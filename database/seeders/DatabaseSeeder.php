<?php

namespace Database\Seeders;

use App\Models\Reward;
use App\Models\Stok;
use App\Models\Layanan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::firstOrCreate(['email' => 'admin@cucisepatu.com'], [
            'name'     => 'Admin',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'aktif'    => true,
        ]);

        // Layanan
        $layanans = [
            ['nama' => 'Cuci Biasa',  'harga' => 25000,  'estimasi_hari' => 2],
            ['nama' => 'Deep Clean',  'harga' => 50000,  'estimasi_hari' => 3],
            ['nama' => 'Premium',     'harga' => 100000, 'estimasi_hari' => 4],
            ['nama' => 'Repaint',     'harga' => 120000, 'estimasi_hari' => 5],
            ['nama' => 'Cuci Sandal', 'harga' => 15000,  'estimasi_hari' => 1],
        ];
        foreach ($layanans as $l) {
            Layanan::firstOrCreate(['nama' => $l['nama']], $l);
        }

        // Stok awal
        $stoks = [
            ['nama' => 'Sabun cuci khusus',   'satuan' => 'liter', 'stok_saat_ini' => 10, 'stok_minimum' => 3, 'harga_satuan' => 35000],
            ['nama' => 'Sikat sol',            'satuan' => 'pcs',   'stok_saat_ini' => 8,  'stok_minimum' => 3, 'harga_satuan' => 15000],
            ['nama' => 'Kain microfiber',      'satuan' => 'pcs',   'stok_saat_ini' => 20, 'stok_minimum' => 5, 'harga_satuan' => 8000],
            ['nama' => 'Cat sepatu putih',     'satuan' => 'botol', 'stok_saat_ini' => 5,  'stok_minimum' => 2, 'harga_satuan' => 45000],
            ['nama' => 'Polish / semir',       'satuan' => 'kaleng','stok_saat_ini' => 6,  'stok_minimum' => 2, 'harga_satuan' => 25000],
            ['nama' => 'Kantong plastik',      'satuan' => 'pcs',   'stok_saat_ini' => 100,'stok_minimum' => 20,'harga_satuan' => 500],
            ['nama' => 'Kertas nota thermal',  'satuan' => 'roll',  'stok_saat_ini' => 4,  'stok_minimum' => 1, 'harga_satuan' => 20000],
        ];
        foreach ($stoks as $s) {
            Stok::firstOrCreate(['nama' => $s['nama']], $s);
        }

        // Reward loyalitas
        $rewards = [
            ['nama' => 'Diskon 10% 1 pasang',  'poin_dibutuhkan' => 50,  'deskripsi' => 'Diskon 10% untuk 1 pasang layanan cuci biasa'],
            ['nama' => 'Gratis cuci 1 pasang', 'poin_dibutuhkan' => 100, 'deskripsi' => 'Gratis cuci biasa 1 pasang sepatu'],
            ['nama' => 'Upgrade ke Deep Clean','poin_dibutuhkan' => 75,  'deskripsi' => 'Upgrade layanan dari cuci biasa ke deep clean gratis'],
            ['nama' => 'Diskon 20% all item',  'poin_dibutuhkan' => 150, 'deskripsi' => 'Diskon 20% untuk semua layanan'],
        ];
        foreach ($rewards as $r) {
            Reward::firstOrCreate(['nama' => $r['nama']], $r);
        }
    }
}

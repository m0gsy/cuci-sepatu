<?php

namespace Tests\Feature;

use App\Models\Bahan;
use App\Models\Stok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BahanStokTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['aktif' => true, 'role' => 'admin']);
        
        \App\Models\RolePermission::create([
            'role' => 'admin',
            'permission' => 'stok',
        ]);
    }

    public function test_can_view_bahans_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('bahans.index'));
        $response->assertStatus(200);
    }

    public function test_can_create_new_bahan(): void
    {
        $this->actingAs($this->admin);

        $bahanData = [
            'nama' => 'Cat Putih Leather',
            'satuan' => 'ml',
            'harga_beli' => 80000,
            'isi_kemasan' => 500,
        ];

        $response = $this->post(route('bahans.store'), $bahanData);
        $response->assertRedirect();

        $this->assertDatabaseHas('bahans', [
            'nama' => 'Cat Putih Leather',
            'satuan' => 'ml',
            'harga_beli' => 80000,
        ]);
    }

    public function test_can_record_manual_stock_incoming_mutation(): void
    {
        $bahan = Bahan::create([
            'nama' => 'Cat Hitam',
            'satuan' => 'ml',
            'harga_beli' => 50000,
            'isi_kemasan' => 100,
        ]);

        $stok = Stok::create([
            'bahan_id' => $bahan->id,
            'stok_saat_ini' => 10.00,
            'stok_minimum' => 2.00,
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('stok.mutasi', $stok), [
            'tipe' => 'masuk',
            'jumlah' => 5.00,
            'keterangan' => 'Beli tambahan',
        ]);

        $response->assertRedirect();
        $this->assertEquals(15.00, $stok->fresh()->stok_saat_ini);
        
        $this->assertDatabaseHas('stok_mutasis', [
            'stok_id' => $stok->id,
            'tipe' => 'masuk',
            'jumlah' => 5.00,
            'stok_sebelum' => 10.00,
            'stok_sesudah' => 15.00,
        ]);
    }

    public function test_can_record_manual_stock_outgoing_mutation(): void
    {
        $bahan = Bahan::create([
            'nama' => 'Glue Premium',
            'satuan' => 'ml',
            'harga_beli' => 90000,
            'isi_kemasan' => 200,
        ]);

        $stok = Stok::create([
            'bahan_id' => $bahan->id,
            'stok_saat_ini' => 20.00,
            'stok_minimum' => 5.00,
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('stok.mutasi', $stok), [
            'tipe' => 'keluar',
            'jumlah' => 3.00,
            'keterangan' => 'Tumpah sedikit',
        ]);

        $response->assertRedirect();
        $this->assertEquals(17.00, $stok->fresh()->stok_saat_ini);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['aktif' => true, 'role' => 'admin']);
    }

    public function test_admin_can_access_pelanggans_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('pelanggans.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_new_pelanggan(): void
    {
        $this->actingAs($this->admin);

        $customerData = [
            'nama' => 'Hendra',
            'no_hp' => '08777888999',
            'alamat' => 'Yogyakarta',
            'catatan' => 'Pelanggan loyal',
        ];

        $response = $this->post(route('pelanggans.store'), $customerData);
        $response->assertRedirect();

        $this->assertDatabaseHas('pelanggans', [
            'nama' => 'Hendra',
            'no_hp' => '628777888999',
            'tier' => 'reguler',
            'poin' => 0,
        ]);
    }

    public function test_tambah_poin_increments_customer_poin_and_saves_history(): void
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'Hendra',
            'no_hp' => '08777888999',
        ]);

        $pelanggan->tambahPoin(50, 'Bonus order baru');

        $this->assertEquals(50, $pelanggan->fresh()->poin);
        $this->assertDatabaseHas('poin_histories', [
            'pelanggan_id' => $pelanggan->id,
            'tipe' => 'tambah',
            'poin' => 50,
            'keterangan' => 'Bonus order baru',
        ]);
    }

    public function test_tukar_poin_decrements_poin_successfully(): void
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'Hendra',
            'no_hp' => '08777888999',
        ]);

        $pelanggan->poin = 100;
        $pelanggan->save();

        $success = $pelanggan->tukarPoin(40, 'Tukar kaos merchandise');

        $this->assertTrue($success);
        $this->assertEquals(60, $pelanggan->fresh()->poin);
        $this->assertDatabaseHas('poin_histories', [
            'pelanggan_id' => $pelanggan->id,
            'tipe' => 'tukar',
            'poin' => 40,
            'keterangan' => 'Tukar kaos merchandise',
        ]);
    }

    public function test_tukar_poin_fails_if_insufficient_poin(): void
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'Hendra',
            'no_hp' => '08777888999',
        ]);

        $pelanggan->poin = 20;
        $pelanggan->save();

        $success = $pelanggan->tukarPoin(50, 'Tukar bonus');

        $this->assertFalse($success);
        $this->assertEquals(20, $pelanggan->fresh()->poin);
    }

    public function test_tier_calculation_updates_tier_correctly(): void
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'Rian',
            'no_hp' => '08222111333',
        ]);

        // Total belanja = 0 -> reguler
        $pelanggan->updateTier();
        $this->assertEquals('reguler', $pelanggan->fresh()->tier);

        // Create paid order of 600,000 IDR (Threshold Silver is 500,000)
        $order = Order::forceCreate([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $this->admin->id,
            'nama_pelanggan' => 'Rian',
            'no_hp' => '08222111333',
            'status' => 'selesai',
            'estimasi_selesai' => now()->addDays(2),
        ]);

        Pembayaran::create([
            'order_id' => $order->id,
            'total' => 600000,
            'status' => 'selesai',
            'metode' => 'cash',
        ]);

        // Recalculate tier
        $pelanggan = $pelanggan->fresh();
        $pelanggan->updateTier();
        $this->assertEquals('silver', $pelanggan->fresh()->tier);
    }
}

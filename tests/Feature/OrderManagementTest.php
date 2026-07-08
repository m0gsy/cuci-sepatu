<?php

namespace Tests\Feature;

use App\Models\JenisBarang;
use App\Models\KategoriLayanan;
use App\Models\Layanan;
use App\Models\Lokasi;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;
    protected KategoriLayanan $category;
    protected JenisBarang $jenisBarang;
    protected Layanan $layanan;
    protected Lokasi $lokasi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['aktif' => true, 'role' => 'owner']);
        $this->admin = User::factory()->create(['aktif' => true, 'role' => 'admin']);

        $this->category = KategoriLayanan::firstOrCreate(['nama' => 'Shoes Care'], ['aktif' => true]);
        $this->jenisBarang = JenisBarang::firstOrCreate(['nama' => 'Sneakers'], ['aktif' => true]);
        $this->layanan = Layanan::firstOrCreate(
            ['nama' => 'Deep Clean', 'kategori_layanan_id' => $this->category->id],
            ['harga' => 50000, 'aktif' => true]
        );
        $this->lokasi = Lokasi::firstOrCreate(
            ['kode' => 'A1'],
            ['nama' => 'Rak A1', 'aktif' => true]
        );
    }

    public function test_owner_and_admin_can_access_orders_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('orders.index'));
        $response->assertStatus(200);

        $this->actingAs($this->owner);
        $response = $this->get(route('orders.index'));
        $response->assertStatus(200);
    }

    public function test_can_create_new_order_with_items(): void
    {
        $this->actingAs($this->admin);

        $orderData = [
            'nama_pelanggan' => 'Reza',
            'no_hp' => '08987654321',
            'lokasi_id' => $this->lokasi->id,
            'catatan_lokasi' => 'Baris 1',
            'estimasi_selesai' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'metode_bayar' => 'cash',
            'items' => [
                [
                    'layanan_id' => $this->layanan->id,
                    'jenis_barang_id' => $this->jenisBarang->id,
                    'jumlah_pasang' => 1,
                    'merek' => 'Nike',
                    'warna' => 'Hitam',
                    'kondisi' => 'Kotor sekali',
                ]
            ]
        ];

        $response = $this->post(route('orders.store'), $orderData);
        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'nama_pelanggan' => 'Reza',
            'no_hp' => '628987654321', // normalized by NormalizePhoneNumber middleware
            'lokasi_id' => $this->lokasi->id,
        ]);

        $order = Order::where('nama_pelanggan', 'Reza')->first();
        $this->assertNotNull($order);
        
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'layanan_id' => $this->layanan->id,
            'merek' => 'Nike',
        ]);

        $this->assertDatabaseHas('pembayarans', [
            'order_id' => $order->id,
            'total' => 50000,
            'status' => 'selesai',
        ]);
    }

    public function test_can_update_order_status(): void
    {
        $order = Order::forceCreate([
            'no_order' => 'ORD-12345',
            'nama_pelanggan' => 'Andi',
            'no_hp' => '08123456789',
            'status' => 'draft',
            'user_id' => $this->admin->id,
            'estimasi_selesai' => now()->addDays(2),
        ]);

        $this->actingAs($this->admin);

        // Update status from draft to menunggu_pembayaran
        $response = $this->patch(route('orders.status', $order), [
            'status' => 'menunggu_pembayaran'
        ]);

        $response->assertRedirect();
        $this->assertEquals('menunggu_pembayaran', $order->fresh()->status);
    }

    public function test_can_update_order_location(): void
    {
        $order = Order::forceCreate([
            'no_order' => 'ORD-54321',
            'nama_pelanggan' => 'Budi',
            'no_hp' => '08123456789',
            'status' => 'draft',
            'user_id' => $this->admin->id,
            'estimasi_selesai' => now()->addDays(2),
        ]);

        $this->actingAs($this->admin);

        $response = $this->patch(route('orders.lokasi', $order), [
            'lokasi_id' => $this->lokasi->id,
            'catatan_lokasi' => 'Rak atas baris ke-3',
        ]);

        $response->assertRedirect();
        $this->assertEquals($this->lokasi->id, $order->fresh()->lokasi_id);
        $this->assertEquals('Rak atas baris ke-3', $order->fresh()->catatan_lokasi);
    }

    public function test_can_create_order_with_point_redemption(): void
    {
        $this->actingAs($this->admin);

        // Create a customer with points
        $pelanggan = \App\Models\Pelanggan::create([
            'nama' => 'Reza',
            'no_hp' => '628987654321',
        ]);
        $pelanggan->tambahPoin(100, 'Saldo Awal');

        $orderData = [
            'nama_pelanggan' => 'Reza',
            'no_hp' => '08987654321', // normalized to 628987654321
            'lokasi_id' => $this->lokasi->id,
            'catatan_lokasi' => 'Baris 1',
            'estimasi_selesai' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'metode_bayar' => 'cash',
            'tukar_poin' => 1,
            'items' => [
                [
                    'layanan_id' => $this->layanan->id,
                    'jenis_barang_id' => $this->jenisBarang->id,
                    'jumlah_pasang' => 1,
                    'merek' => 'Nike',
                    'warna' => 'Hitam',
                    'kondisi' => 'Kotor sekali',
                ]
            ]
        ];

        $response = $this->post(route('orders.store'), $orderData);
        $response->assertRedirect();

        $order = Order::where('nama_pelanggan', 'Reza')->first();
        $this->assertNotNull($order);

        // Check database fields
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'poin_digunakan' => 100,
            'diskon_poin' => 10000,
        ]);

        // Check payment total (Deep Clean = 50000 - 10000 = 40000)
        $this->assertDatabaseHas('pembayarans', [
            'order_id' => $order->id,
            'total' => 40000,
        ]);

        // Check customer points decreased
        $this->assertEquals(0, $pelanggan->fresh()->poin);
    }

    public function test_can_refund_points_on_order_cancel(): void
    {
        $this->actingAs($this->admin);

        $pelanggan = \App\Models\Pelanggan::create([
            'nama' => 'Reza',
            'no_hp' => '628987654321',
        ]);
        
        $order = Order::forceCreate([
            'no_order' => 'ORD-9999',
            'nama_pelanggan' => 'Reza',
            'no_hp' => '628987654321',
            'status' => 'draft',
            'user_id' => $this->admin->id,
            'pelanggan_id' => $pelanggan->id,
            'estimasi_selesai' => now()->addDays(2),
            'poin_digunakan' => 50,
            'diskon_poin' => 5000,
        ]);

        // Set customer points to 0 to simulate spent
        $pelanggan->poin = 0;
        $pelanggan->save();

        // Cancel the order
        $response = $this->patch(route('orders.status', $order), [
            'status' => 'batal'
        ]);

        $response->assertRedirect();
        
        // Assert points are refunded
        $this->assertEquals(50, $pelanggan->fresh()->poin);
    }
}


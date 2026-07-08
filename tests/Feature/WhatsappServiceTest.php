<?php

namespace Tests\Feature;

use App\Models\JenisBarang;
use App\Models\KategoriLayanan;
use App\Models\Layanan;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pembayaran;
use App\Models\User;
use App\Services\WhatsappService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsappServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WhatsappService $whatsappService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->whatsappService = new WhatsappService();

        // Create a test user
        $this->user = User::factory()->create([
            'aktif' => true,
            'role' => 'admin',
        ]);

        // Seed default templates
        \Artisan::call('db:seed', ['--class' => 'WaTemplateSeeder']);
    }

    public function test_it_compiles_whatsapp_message_for_multi_item_order(): void
    {
        // 1. Create dependencies
        $category = KategoriLayanan::firstOrCreate(['nama' => 'Care'], ['aktif' => true]);
        $jenis1 = JenisBarang::firstOrCreate(['nama' => 'Sneakers'], ['aktif' => true]);
        $jenis2 = JenisBarang::firstOrCreate(['nama' => 'Sandal'], ['aktif' => true]);

        $layanan1 = Layanan::firstOrCreate(
            ['nama' => 'Cuci Biasa', 'kategori_layanan_id' => $category->id],
            ['harga' => 30000, 'aktif' => true]
        );
        $layanan2 = Layanan::firstOrCreate(
            ['nama' => 'Cuci Sandal', 'kategori_layanan_id' => $category->id],
            ['harga' => 20000, 'aktif' => true]
        );

        // 2. Create order using forceCreate to avoid mass assignment filters
        $order = Order::forceCreate([
            'nama_pelanggan' => 'Andi',
            'no_hp' => '08123456789',
            'status' => 'draft',
            'user_id' => $this->user->id,
            'estimasi_selesai' => now()->addDays(2),
        ]);

        // 3. Create items
        OrderItem::create([
            'order_id' => $order->id,
            'layanan_id' => $layanan1->id,
            'jenis_barang_id' => $jenis1->id,
            'jumlah_pasang' => 1,
            'harga_satuan' => 30000,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'layanan_id' => $layanan2->id,
            'jenis_barang_id' => $jenis2->id,
            'jumlah_pasang' => 2,
            'harga_satuan' => 20000,
        ]);

        // Create Pembayaran
        Pembayaran::create([
            'order_id' => $order->id,
            'total' => 70000, // 30k + 2 * 20k
            'metode' => 'tunai',
            'status' => 'belum_selesai',
        ]);

        // Refresh model relations
        $order->load('items.layanan', 'pembayaran');

        // 4. Compile messages
        $messageMasuk = $this->whatsappService->pesanOrderMasuk($order);
        $messageInvoice = $this->whatsappService->pesanInvoice($order);

        // 5. Assertions
        $this->assertStringContainsString('Andi', $messageMasuk);
        $this->assertStringContainsString($order->no_order, $messageMasuk);
        $this->assertStringContainsString('- Cuci Biasa (Sneakers): 1 pasang x Rp 30.000', $messageMasuk);
        $this->assertStringContainsString('- Cuci Sandal (Sandal): 2 pasang x Rp 20.000', $messageMasuk);
        $this->assertStringContainsString('Rp 70.000', $messageMasuk);

        $this->assertStringContainsString('Rincian Order', $messageInvoice);
        $this->assertStringContainsString('- Cuci Biasa (Sneakers): 1 pasang x Rp 30.000', $messageInvoice);
        $this->assertStringContainsString('- Cuci Sandal (Sandal): 2 pasang x Rp 20.000', $messageInvoice);
        $this->assertStringContainsString('Rp 70.000', $messageInvoice);
    }

    public function test_it_compiles_whatsapp_message_for_legacy_single_item_order(): void
    {
        // 1. Create dependencies
        $category = KategoriLayanan::firstOrCreate(['nama' => 'Care'], ['aktif' => true]);
        $layanan = Layanan::firstOrCreate(
            ['nama' => 'Cuci Premium', 'kategori_layanan_id' => $category->id],
            ['harga' => 50000, 'aktif' => true]
        );

        // 2. Create legacy order using forceCreate
        $order = Order::forceCreate([
            'nama_pelanggan' => 'Budi',
            'no_hp' => '087654321',
            'status' => 'draft',
            'layanan_id' => $layanan->id,
            'jenis_sepatu' => 'Canvas',
            'jumlah_pasang' => 1,
            'harga_satuan' => 50000,
            'user_id' => $this->user->id,
            'estimasi_selesai' => now()->addDays(1),
        ]);

        // Create Pembayaran
        Pembayaran::create([
            'order_id' => $order->id,
            'total' => 50000,
            'metode' => 'tempo',
            'status' => 'belum_selesai',
        ]);

        // Refresh relations
        $order->load('layanan', 'pembayaran');

        // 3. Compile messages
        $messageMasuk = $this->whatsappService->pesanOrderMasuk($order);
        $messageInvoice = $this->whatsappService->pesanInvoice($order);

        // 4. Assertions
        $this->assertStringContainsString('Budi', $messageMasuk);
        $this->assertStringContainsString($order->no_order, $messageMasuk);
        $this->assertStringContainsString('- Cuci Premium (Canvas): 1 pasang x Rp 50.000', $messageMasuk);
        $this->assertStringContainsString('Rp 50.000', $messageMasuk);

        $this->assertStringContainsString('- Cuci Premium (Canvas): 1 pasang x Rp 50.000', $messageInvoice);
    }
}

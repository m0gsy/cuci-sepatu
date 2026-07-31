<?php

namespace Tests\Feature;

use App\Models\JenisBarang;
use App\Models\KategoriLayanan;
use App\Models\Layanan;
use App\Models\Order;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['aktif' => true, 'role' => 'admin']);
        $category = KategoriLayanan::firstOrCreate(['nama' => 'Shoes Care'], ['aktif' => true]);
        $jenisBarang = JenisBarang::firstOrCreate(['nama' => 'Sneakers'], ['aktif' => true]);
        $layanan = Layanan::firstOrCreate(
            ['nama' => 'Deep Clean', 'kategori_layanan_id' => $category->id],
            ['harga' => 50000, 'aktif' => true]
        );

        $pelanggan = Pelanggan::create([
            'nama' => 'Budi Santoso',
            'no_hp' => '628123456789',
        ]);

        $this->order = Order::create([
            'no_order' => 'ORD-TEST-001',
            'nama_pelanggan' => 'Budi Santoso',
            'no_hp' => '628123456789',
            'status' => 'diproses',
            'user_id' => $this->admin->id,
            'pelanggan_id' => $pelanggan->id,
            'estimasi_selesai' => now()->addDays(2),
        ]);

        $this->order->items()->create([
            'layanan_id' => $layanan->id,
            'jenis_barang_id' => $jenisBarang->id,
            'jumlah_pasang' => 1,
            'harga_satuan' => 50000,
            'merek' => 'Adidas',
            'warna' => 'Putih',
        ]);

        Pembayaran::create([
            'order_id' => $this->order->id,
            'total' => 50000,
            'metode' => 'qris',
            'status' => 'selesai',
            'dibayar_pada' => now(),
        ]);
    }

    public function test_authenticated_staff_can_download_order_invoice_pdf(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('orders.invoice', $this->order));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment; filename="invoice-ORD-TEST-001.pdf"', (string) $response->headers->get('content-disposition'));
    }

    public function test_public_customer_can_download_invoice_pdf_using_tracking_token(): void
    {
        $response = $this->get(route('status.invoice', $this->order->token_publik));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment; filename="invoice-ORD-TEST-001.pdf"', (string) $response->headers->get('content-disposition'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Bahan;
use App\Models\JenisBarang;
use App\Models\KategoriLayanan;
use App\Models\Layanan;
use App\Models\LayananRecipe;
use App\Models\Order;
use App\Models\Stok;
use App\Models\User;
use App\Services\StockAutomationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderStockAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected KategoriLayanan $category;

    protected JenisBarang $jenisBarang;

    protected Layanan $layanan;

    protected Bahan $bahan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'aktif' => true,
            'role' => 'admin',
        ]);

        $this->category = KategoriLayanan::firstOrCreate(
            ['nama' => 'Umum'],
            ['aktif' => true]
        );

        $this->jenisBarang = JenisBarang::firstOrCreate(
            ['nama' => 'Sneakers'],
            ['aktif' => true]
        );

        $this->layanan = Layanan::create([
            'nama' => 'Deep Clean',
            'kategori_layanan_id' => $this->category->id,
            'harga' => 50000,
            'estimasi_hari' => 3,
            'durasi_satuan' => 'hari',
            'aktif' => true,
        ]);

        $this->bahan = Bahan::create([
            'nama' => 'Sabun Khusus',
            'satuan' => 'ml',
            'harga_beli' => 100000,
            'isi_kemasan' => 1000, // harga_satuan = 100 / ml
        ]);

        Stok::create([
            'bahan_id' => $this->bahan->id,
            'stok_saat_ini' => 100.00,
            'stok_minimum' => 10.00,
        ]);

        // Create LayananRecipe: deep clean consumes 10ml per pair
        LayananRecipe::create([
            'layanan_id' => $this->layanan->id,
            'bahan_id' => $this->bahan->id,
            'jumlah_penggunaan' => 10.00,
        ]);
    }

    public function test_order_creation_deducts_stock_and_creates_mutations()
    {
        $this->actingAs($this->user);

        // Expected deduction: 2 pairs * 10 ml = 20 ml. Remaining: 80 ml.
        $payload = [
            'nama_pelanggan' => 'John Doe',
            'no_hp' => '08123456789',
            'estimasi_selesai' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'metode_bayar' => 'cash',
            'items' => [
                [
                    'layanan_id' => $this->layanan->id,
                    'jenis_barang_id' => $this->jenisBarang->id,
                    'jumlah_pasang' => 2,
                    'merek' => 'Nike',
                    'warna' => 'Hitam',
                    'kondisi' => 'Kotor',
                ],
            ],
        ];

        $response = $this->post(route('orders.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'nama_pelanggan' => 'John Doe',
            'status' => 'draft', // defaults to draft
        ]);

        $order = Order::first();
        $this->assertCount(1, $order->items);
        $this->assertEquals(2, $order->jumlah_pasang);

        // Check stock is deducted: 100.00 - 20.00 = 80.00
        $this->bahan->stok->refresh();
        $this->assertEquals(80.00, $this->bahan->stok->stok_saat_ini);

        // Check mutation is created
        $this->assertDatabaseHas('stok_mutasis', [
            'stok_id' => $this->bahan->stok->id,
            'tipe' => 'keluar',
            'jumlah' => 20.00,
        ]);
    }

    public function test_insufficient_stock_raises_warning_in_session()
    {
        $this->actingAs($this->user);

        // Set stock to only 5 ml
        $this->bahan->stok->update(['stok_saat_ini' => 5.00]);

        // Needs 2 pairs * 10 ml = 20 ml. Short by 15 ml.
        $payload = [
            'nama_pelanggan' => 'Jane Doe',
            'no_hp' => '08123456780',
            'estimasi_selesai' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'metode_bayar' => 'cash',
            'items' => [
                [
                    'layanan_id' => $this->layanan->id,
                    'jenis_barang_id' => $this->jenisBarang->id,
                    'jumlah_pasang' => 2,
                    'merek' => 'Adidas',
                    'warna' => 'Putih',
                    'kondisi' => 'Noda tanah',
                ],
            ],
        ];

        $response = $this->post(route('orders.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        // Stock goes to 0 (since max(0, ...))
        $this->bahan->stok->refresh();
        $this->assertEquals(0.00, $this->bahan->stok->stok_saat_ini);
    }

    public function test_cancelling_order_reverses_stock_deduction()
    {
        $this->actingAs($this->user);

        // Create order directly
        $order = Order::create([
            'user_id' => $this->user->id,
            'nama_pelanggan' => 'Alice',
            'no_hp' => '08123456781',
            'status' => 'draft',
            'estimasi_selesai' => now()->addDays(3),
        ]);

        $item = $order->items()->create([
            'layanan_id' => $this->layanan->id,
            'jenis_barang_id' => $this->jenisBarang->id,
            'jumlah_pasang' => 2,
            'harga_satuan' => 50000,
            'hpp' => 2000,
        ]);

        app(StockAutomationService::class)
            ->deductStock($order->load('items.layanan'));
        $this->assertEquals(80, $this->bahan->stok->fresh()->stok_saat_ini);

        // Transition status to batal (cancelled)
        $response = $this->patch(route('orders.status', $order), [
            'status' => 'batal',
        ]);

        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals('batal', $order->status);

        // Stock reversed: 80.00 + 20.00 = 100.00
        $this->bahan->stok->refresh();
        $this->assertEquals(100.00, $this->bahan->stok->stok_saat_ini);

        // Check mutation is created for incoming reversion
        $this->assertDatabaseHas('stok_mutasis', [
            'stok_id' => $this->bahan->stok->id,
            'tipe' => 'masuk',
            'jumlah' => 20.00,
            'keterangan' => "Pengembalian stok pembatalan order {$order->no_order}",
        ]);
    }

    public function test_partial_deduction_reverses_only_the_amount_actually_removed(): void
    {
        $this->actingAs($this->user);
        $this->bahan->stok->update(['stok_saat_ini' => 5]);

        $payload = [
            'idempotency_key' => (string) Str::uuid(),
            'nama_pelanggan' => 'Partial Stock',
            'no_hp' => '08123456780',
            'estimasi_selesai' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'metode_bayar' => 'cash',
            'items' => [[
                'layanan_id' => $this->layanan->id,
                'jenis_barang_id' => $this->jenisBarang->id,
                'jumlah_pasang' => 2,
            ]],
        ];

        $this->post(route('orders.store'), $payload)->assertRedirect();
        $order = Order::where('nama_pelanggan', 'Partial Stock')->firstOrFail();
        $this->assertEquals(0, $this->bahan->stok->fresh()->stok_saat_ini);

        $this->patch(route('orders.status', $order), ['status' => 'batal'])
            ->assertRedirect();

        $this->assertEquals(5, $this->bahan->stok->fresh()->stok_saat_ini);
        $this->assertDatabaseHas('stok_mutasis', [
            'order_id' => $order->id,
            'tipe' => 'keluar',
            'jumlah' => 5,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\KategoriLayanan;
use App\Models\Layanan;
use App\Models\Lokasi;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LokasiHppTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $admin;

    protected Layanan $layanan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['aktif' => true, 'role' => 'owner']);
        $this->admin = User::factory()->create(['aktif' => true, 'role' => 'admin']);

        // Set up permissions for admin
        RolePermission::firstOrCreate([
            'role' => 'admin',
            'permission' => 'lokasi',
        ]);
        RolePermission::firstOrCreate([
            'role' => 'admin',
            'permission' => 'hpp',
        ]);

        $category = KategoriLayanan::firstOrCreate(['nama' => 'Care'], ['aktif' => true]);
        $this->layanan = Layanan::firstOrCreate(
            ['nama' => 'Cuci Biasa', 'kategori_layanan_id' => $category->id],
            ['harga' => 30000, 'aktif' => true]
        );
    }

    public function test_can_view_lokasi_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('lokasi.index'));
        $response->assertStatus(200);
    }

    public function test_can_create_new_lokasi(): void
    {
        $this->actingAs($this->admin);

        $lokasiData = [
            'kode' => 'B2',
            'nama' => 'Rak B2',
            'harga_tambahan' => 5000,
            'harga_custom' => true,
            'deskripsi' => 'Rak Tengah',
        ];

        $response = $this->post(route('lokasi.store'), $lokasiData);
        $response->assertRedirect();

        $this->assertDatabaseHas('lokasis', [
            'kode' => 'B2',
            'nama' => 'Rak B2',
            'harga_tambahan' => 5000,
            'harga_custom' => true,
        ]);
    }

    public function test_can_set_specific_service_price_on_lokasi(): void
    {
        $lokasi = Lokasi::create([
            'kode' => 'C3',
            'nama' => 'Rak C3',
            'aktif' => true,
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('lokasi.harga-layanan.set', $lokasi), [
            'layanan_id' => $this->layanan->id,
            'harga' => 45000,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('lokasi_layanan', [
            'lokasi_id' => $lokasi->id,
            'layanan_id' => $this->layanan->id,
            'harga' => 45000,
        ]);
    }

    public function test_can_retrieve_pricing_for_lokasi_via_api(): void
    {
        $lokasi = Lokasi::create([
            'kode' => 'D4',
            'nama' => 'Rak D4',
            'harga_tambahan' => 5000,
            'harga_custom' => true,
            'aktif' => true,
        ]);

        // Attach custom override price for $this->layanan
        $lokasi->layanans()->attach($this->layanan->id, ['harga' => 35000]);

        $this->actingAs($this->admin);

        // Fetching pricing override
        $response = $this->get(route('lokasi.harga', [
            'lokasi' => $lokasi->id,
            'layanan_id' => $this->layanan->id,
            'harga_layanan' => 30000,
        ]));

        $response->assertStatus(200);
        $response->assertJsonPath('harga_efektif', 35000);
    }

    public function test_owner_can_access_hpp_reports(): void
    {
        $this->actingAs($this->owner);
        $response = $this->get(route('hpp.laporan'));
        $response->assertStatus(200);

        $response2 = $this->get(route('hpp.index'));
        $response2->assertStatus(200);
    }
}

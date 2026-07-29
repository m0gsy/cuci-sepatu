<?php

namespace Tests\Feature;

use App\Models\Operasional;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanOperasionalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['aktif' => true, 'role' => 'admin']);

        RolePermission::create([
            'role' => 'admin',
            'permission' => 'laporan',
        ]);
        RolePermission::create([
            'role' => 'admin',
            'permission' => 'operasional',
        ]);
    }

    public function test_can_view_laporan_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('laporan'));
        $response->assertStatus(200);
    }

    public function test_can_export_laporan_pdf(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('laporan.pdf'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_can_log_new_operational_expense(): void
    {
        $this->actingAs($this->admin);

        $expenseData = [
            'nama' => 'Beli sapu dan pel',
            'kategori' => 'peralatan',
            'jumlah' => 45000,
            'tanggal' => now()->format('Y-m-d'),
            'catatan' => 'Keperluan kebersihan toko',
        ];

        $response = $this->post(route('operasional.store'), $expenseData);
        $response->assertRedirect();

        $this->assertDatabaseHas('operasionals', [
            'nama' => 'Beli sapu dan pel',
            'jumlah' => 45000,
        ]);
    }

    public function test_can_delete_operational_expense(): void
    {
        $expense = Operasional::forceCreate([
            'nama' => 'Beli air galon',
            'kategori' => 'lainnya',
            'jumlah' => 20000,
            'tanggal' => now()->format('Y-m-d'),
            'user_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin);

        $response = $this->delete(route('operasional.destroy', $expense));
        $response->assertRedirect();

        $this->assertDatabaseMissing('operasionals', [
            'id' => $expense->id,
        ]);
    }
}

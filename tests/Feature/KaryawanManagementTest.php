<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RolePermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaryawanManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['aktif' => true, 'role' => 'owner']);
        $this->admin = User::factory()->create(['aktif' => true, 'role' => 'admin']);
    }

    public function test_owner_can_access_karyawans_index(): void
    {
        $this->actingAs($this->owner);
        $response = $this->get(route('karyawans.index'));
        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_karyawans_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('karyawans.index'));
        $response->assertStatus(403); // owner only middleware
    }

    public function test_owner_can_create_new_karyawan(): void
    {
        $this->actingAs($this->owner);

        $employeeData = [
            'name' => 'Karyawan Baru',
            'email' => 'karyawan@cucisepatu.com',
            'password' => 'karyawan123!',
            'password_confirmation' => 'karyawan123!',
            'role' => 'cleaner',
            'no_hp' => '08222333444',
            'alamat' => 'Sleman, DIY',
        ];

        $response = $this->post(route('karyawans.store'), $employeeData);
        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'name' => 'Karyawan Baru',
            'email' => 'karyawan@cucisepatu.com',
            'role' => 'cleaner',
        ]);
    }

    public function test_owner_can_toggle_karyawan_status(): void
    {
        $karyawan = User::factory()->create([
            'aktif' => true,
            'role' => 'cleaner',
        ]);

        $this->actingAs($this->owner);

        $response = $this->patch(route('karyawans.toggle', $karyawan));
        $response->assertRedirect();
        
        $this->assertFalse($karyawan->fresh()->aktif);
    }

    public function test_owner_can_reset_karyawan_password(): void
    {
        $karyawan = User::factory()->create([
            'aktif' => true,
            'role' => 'cleaner',
        ]);

        $this->actingAs($this->owner);

        $response = $this->patch(route('karyawans.password', $karyawan), [
            'password' => 'newpassword123!',
            'password_confirmation' => 'newpassword123!',
        ]);

        $response->assertRedirect();
        $this->assertTrue(\Hash::check('newpassword123!', $karyawan->fresh()->password));
    }

    public function test_owner_can_save_role_permissions(): void
    {
        $this->actingAs($this->owner);

        $permissionsData = [
            'permissions' => [
                'admin' => [
                    'orders.manage',
                    'pelanggan',
                    'lokasi'
                ],
                'cleaner' => []
            ]
        ];

        $response = $this->post(route('karyawans.permissions'), $permissionsData);
        $response->assertRedirect();

        $this->assertDatabaseHas('role_permissions', [
            'role' => 'admin',
            'permission' => 'orders.manage',
        ]);
        $this->assertDatabaseHas('role_permissions', [
            'role' => 'admin',
            'permission' => 'pelanggan',
        ]);
    }
}

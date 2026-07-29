<?php

namespace Tests\Feature;

use App\Models\RolePermission;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Assigning vouchers permission to admin role
        $this->admin = User::factory()->create(['aktif' => true, 'role' => 'admin']);

        RolePermission::create([
            'role' => 'admin',
            'permission' => 'vouchers',
        ]);
    }

    public function test_can_view_vouchers_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('vouchers.index'));
        $response->assertStatus(200);
    }

    public function test_can_create_new_voucher(): void
    {
        $this->actingAs($this->admin);

        $voucherData = [
            'kode' => 'PROMO10',
            'tipe' => 'persen',
            'nilai' => 10,
            'min_transaksi' => 50000,
            'kuota' => 100,
            'expired_at' => now()->addDays(10)->format('Y-m-d'),
            'deskripsi' => 'Diskon 10%',
        ];

        $response = $this->post(route('vouchers.store'), $voucherData);
        $response->assertRedirect();

        $this->assertDatabaseHas('vouchers', [
            'kode' => 'PROMO10',
            'tipe' => 'persen',
            'nilai' => 10,
            'min_transaksi' => 50000,
            'aktif' => true,
        ]);
    }

    public function test_can_toggle_voucher_status(): void
    {
        $voucher = Voucher::create([
            'kode' => 'OFFLINE',
            'tipe' => 'nominal',
            'nilai' => 15000,
            'aktif' => true,
        ]);

        $this->actingAs($this->admin);

        $response = $this->patch(route('vouchers.toggle', $voucher));
        $response->assertRedirect();
        $this->assertFalse($voucher->fresh()->aktif);
    }

    public function test_can_validate_active_voucher_code(): void
    {
        $voucher = Voucher::create([
            'kode' => 'PROMO20K',
            'tipe' => 'nominal',
            'nilai' => 20000,
            'min_transaksi' => 100000,
            'aktif' => true,
        ]);

        $this->actingAs($this->admin);

        // Valid voucher
        $response = $this->get(route('vouchers.cek', [
            'kode' => 'PROMO20K',
            'total' => 150000,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'diskon' => 20000,
        ]);
    }

    public function test_validation_fails_for_expired_or_invalid_voucher(): void
    {
        $voucher = Voucher::create([
            'kode' => 'EXPIRED',
            'tipe' => 'persen',
            'nilai' => 10,
            'expired_at' => now()->subDay(),
            'aktif' => true,
        ]);

        $this->actingAs($this->admin);

        // Expired
        $response = $this->get(route('vouchers.cek', [
            'kode' => 'EXPIRED',
            'total' => 50000,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => false,
        ]);

        // Below minimum transaction amount
        $voucher2 = Voucher::create([
            'kode' => 'BIGSPEND',
            'tipe' => 'nominal',
            'nilai' => 50000,
            'min_transaksi' => 200000,
            'aktif' => true,
        ]);

        $response2 = $this->get(route('vouchers.cek', [
            'kode' => 'BIGSPEND',
            'total' => 100000,
        ]));

        $response2->assertStatus(200);
        $response2->assertJson([
            'valid' => false,
        ]);
    }

    public function test_voucher_code_is_normalized_and_percentage_cannot_exceed_one_hundred(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('vouchers.store'), [
            'kode' => 'promo10',
            'tipe' => 'persen',
            'nilai' => 10,
        ])->assertRedirect();
        $this->assertDatabaseHas('vouchers', ['kode' => 'PROMO10']);

        $this->post(route('vouchers.store'), [
            'kode' => 'OVER100',
            'tipe' => 'persen',
            'nilai' => 101,
        ])->assertSessionHasErrors('nilai');
    }

    public function test_voucher_is_valid_through_its_expiration_date(): void
    {
        $voucher = Voucher::create([
            'kode' => 'TODAY',
            'tipe' => 'nominal',
            'nilai' => 1000,
            'expired_at' => today(),
            'aktif' => true,
        ]);

        $this->assertTrue($voucher->masihBerlaku(10000));
    }
}

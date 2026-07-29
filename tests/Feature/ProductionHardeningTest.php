<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Pelanggan;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_lookup_requires_order_permission(): void
    {
        $user = User::factory()->create(['aktif' => true, 'role' => 'cleaner']);
        Pelanggan::create(['nama' => 'Private', 'no_hp' => '628111111111']);

        $this->actingAs($user)
            ->getJson(route('pelanggans.cari', ['q' => 'Private']))
            ->assertForbidden();

        RolePermission::create(['role' => 'cleaner', 'permission' => 'orders.manage']);
        RolePermission::bustCache('cleaner');

        $this->getJson(route('pelanggans.cari', ['q' => 'Private']))
            ->assertOk();
    }

    public function test_contact_message_is_persisted_before_success_is_shown(): void
    {
        $this->post(route('contact.submit'), [
            'name' => 'Visitor',
            'email' => 'visitor@example.com',
            'subject' => 'Pertanyaan',
            'message' => 'Apakah buka hari Minggu?',
        ])->assertRedirect()->assertSessionHas('contact_success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'visitor@example.com',
            'message' => 'Apakah buka hari Minggu?',
        ]);
    }

    public function test_only_owner_can_view_contact_messages(): void
    {
        $admin = User::factory()->create(['aktif' => true, 'role' => 'admin']);
        $owner = User::factory()->create(['aktif' => true, 'role' => 'owner']);

        $this->actingAs($admin)->get(route('contact-messages.index'))->assertForbidden();
        $this->actingAs($owner)->get(route('contact-messages.index'))->assertOk();
    }

    public function test_inactive_user_session_cannot_reach_password_routes(): void
    {
        $user = User::factory()->create(['aktif' => false]);

        $this->actingAs($user)
            ->get(route('password.confirm'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_public_review_requires_completed_order(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'nama_pelanggan' => 'Pelanggan',
            'no_hp' => '628111111111',
            'status' => 'batal',
            'estimasi_selesai' => now(),
        ]);

        $this->post(route('orders.review.store', ['order' => $order->token_publik]), [
            'rating' => 5,
            'ulasan' => 'Tidak boleh tersimpan.',
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('reviews', ['order_id' => $order->id]);
    }

    public function test_production_preflight_fails_closed_in_test_environment(): void
    {
        $this->artisan('app:production-check')
            ->expectsOutputToContain('Production preflight failed.')
            ->assertFailed();
    }
}

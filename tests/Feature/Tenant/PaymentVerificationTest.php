<?php

namespace Tests\Feature\Tenant;

use App\Models\Booking;
use App\Models\Kost;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    private function createPaymentForTenant(User $tenant, string $status = 'pending_verification'): Payment
    {
        $kost = Kost::factory()->create(['user_id' => $tenant->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);
        $price = RoomPrice::factory()->create(['room_id' => $room->id]);
        $pengguna = User::factory()->pengguna()->create();
        $booking = Booking::factory()->active()->create([
            'user_id' => $pengguna->id,
            'room_id' => $room->id,
            'room_price_id' => $price->id,
        ]);

        return Payment::factory()->create([
            'booking_id' => $booking->id,
            'status' => $status,
        ]);
    }

    public function test_tenant_can_approve_payment(): void
    {
        $tenant = User::factory()->tenant()->create();
        $payment = $this->createPaymentForTenant($tenant, 'pending_verification');

        $response = $this->actingAs($tenant)->patch(route('dashboard.tenant.payments.approve', $payment));
        $response->assertRedirect();
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
    }

    public function test_tenant_can_decline_payment_with_notes(): void
    {
        $tenant = User::factory()->tenant()->create();
        $payment = $this->createPaymentForTenant($tenant, 'pending_verification');

        $response = $this->actingAs($tenant)->patch(route('dashboard.tenant.payments.decline', $payment), [
            'notes' => 'Bukti pembayaran tidak jelas',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'declined',
            'decline_notes' => 'Bukti pembayaran tidak jelas',
        ]);
    }

    public function test_tenant_cannot_approve_unpaid_payment(): void
    {
        $tenant = User::factory()->tenant()->create();
        $payment = $this->createPaymentForTenant($tenant, 'unpaid');

        $response = $this->actingAs($tenant)->patch(route('dashboard.tenant.payments.approve', $payment));
        $response->assertStatus(422);
    }

    public function test_tenant_cannot_approve_other_tenants_payment(): void
    {
        $tenant = User::factory()->tenant()->create();
        $otherTenant = User::factory()->tenant()->create();
        $payment = $this->createPaymentForTenant($otherTenant, 'pending_verification');

        $response = $this->actingAs($tenant)->patch(route('dashboard.tenant.payments.approve', $payment));
        $response->assertStatus(403);
    }
}

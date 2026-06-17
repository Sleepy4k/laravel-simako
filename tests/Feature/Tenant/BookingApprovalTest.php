<?php

namespace Tests\Feature\Tenant;

use App\Models\Booking;
use App\Models\Kost;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    private function createBookingForTenant(User $tenant, string $status = 'pending'): Booking
    {
        $kost = Kost::factory()->create(['user_id' => $tenant->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id, 'is_available' => true]);
        $price = RoomPrice::factory()->create(['room_id' => $room->id]);
        $pengguna = User::factory()->pengguna()->create();

        return Booking::factory()->create([
            'user_id' => $pengguna->id,
            'room_id' => $room->id,
            'room_price_id' => $price->id,
            'status' => $status,
        ]);
    }

    public function test_tenant_can_view_bookings(): void
    {
        $tenant = User::factory()->tenant()->create();

        $response = $this->actingAs($tenant)->get(route('dashboard.tenant.bookings.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Tenant/Bookings/Index'));
    }

    public function test_tenant_can_approve_pending_booking(): void
    {
        $tenant = User::factory()->tenant()->create();
        $booking = $this->createBookingForTenant($tenant);

        $response = $this->actingAs($tenant)->patch(route('dashboard.tenant.bookings.approve', $booking));
        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'approved']);
        $this->assertDatabaseHas('payments', ['booking_id' => $booking->id]);
        $this->assertDatabaseHas('message_threads', ['booking_id' => $booking->id]);
    }

    public function test_tenant_can_reject_pending_booking(): void
    {
        $tenant = User::factory()->tenant()->create();
        $booking = $this->createBookingForTenant($tenant);

        $response = $this->actingAs($tenant)->patch(route('dashboard.tenant.bookings.reject', $booking), [
            'reason' => 'Kamar sudah tidak tersedia',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
    }

    public function test_tenant_cannot_approve_booking_of_other_tenant(): void
    {
        $tenant = User::factory()->tenant()->create();
        $otherTenant = User::factory()->tenant()->create();
        $booking = $this->createBookingForTenant($otherTenant);

        $response = $this->actingAs($tenant)->patch(route('dashboard.tenant.bookings.approve', $booking));
        $response->assertStatus(403);
    }
}

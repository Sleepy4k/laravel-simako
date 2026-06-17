<?php

namespace Tests\Feature\User;

use App\Models\Booking;
use App\Models\Kost;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    private function createRoomWithPrice(array $kostAttrs = []): array
    {
        $tenant = User::factory()->tenant()->create();
        $kost = Kost::factory()->create(array_merge(['user_id' => $tenant->id, 'status' => 'active'], $kostAttrs));
        $room = Room::factory()->create(['kost_id' => $kost->id, 'is_available' => true]);
        $price = RoomPrice::factory()->create(['room_id' => $room->id, 'period' => 'monthly', 'price' => 800000]);

        return [$room, $price, $kost];
    }

    public function test_user_can_view_bookings(): void
    {
        $user = User::factory()->pengguna()->create();

        $response = $this->actingAs($user)->get(route('dashboard.bookings.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('User/Bookings/Index'));
    }

    public function test_user_can_view_create_booking_form(): void
    {
        $user = User::factory()->pengguna()->create();
        [$room] = $this->createRoomWithPrice();

        $response = $this->actingAs($user)->get(route('dashboard.bookings.create', $room));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('User/Bookings/Create'));
    }

    public function test_user_can_create_booking(): void
    {
        $user = User::factory()->pengguna()->create();
        [$room, $price] = $this->createRoomWithPrice();

        $response = $this->actingAs($user)->post(route('dashboard.bookings.store'), [
            'room_id' => $room->id,
            'room_price_id' => $price->id,
            'start_date' => now()->addDays(7)->toDateString(),
            'notes' => 'Mahasiswa semester 4',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'room_id' => $room->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_can_cancel_pending_booking(): void
    {
        $user = User::factory()->pengguna()->create();
        [$room, $price] = $this->createRoomWithPrice();
        $booking = Booking::factory()->pending()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'room_price_id' => $price->id,
        ]);

        $response = $this->actingAs($user)->patch(route('dashboard.bookings.cancel', $booking));
        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
    }

    public function test_user_cannot_cancel_active_booking_of_another_user(): void
    {
        $user = User::factory()->pengguna()->create();
        $otherUser = User::factory()->pengguna()->create();
        [$room, $price] = $this->createRoomWithPrice();
        $booking = Booking::factory()->pending()->create([
            'user_id' => $otherUser->id,
            'room_id' => $room->id,
            'room_price_id' => $price->id,
        ]);

        $response = $this->actingAs($user)->patch(route('dashboard.bookings.cancel', $booking));
        $response->assertStatus(403);
    }
}

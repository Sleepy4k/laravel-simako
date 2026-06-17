<?php

namespace Tests\Feature\User;

use App\Models\Booking;
use App\Models\Kost;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    private function createThread(): array
    {
        $tenant = User::factory()->tenant()->create();
        $user = User::factory()->pengguna()->create();
        $kost = Kost::factory()->create(['user_id' => $tenant->id, 'status' => 'active']);
        $room = Room::factory()->create(['kost_id' => $kost->id, 'is_available' => true]);
        $price = RoomPrice::factory()->create(['room_id' => $room->id, 'period' => 'monthly']);

        $booking = Booking::factory()->active()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'room_price_id' => $price->id,
        ]);

        $thread = MessageThread::create([
            'booking_id' => $booking->id,
        ]);

        return [$thread, $user, $tenant, $booking];
    }

    public function test_user_can_view_message_threads(): void
    {
        [$thread, $user] = $this->createThread();

        $response = $this->actingAs($user)->get(route('dashboard.messages.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('User/Messages/Index'));
    }

    public function test_user_can_view_single_thread(): void
    {
        [$thread, $user] = $this->createThread();

        $response = $this->actingAs($user)->get(route('dashboard.messages.show', $thread));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('User/Messages/Show'));
    }

    public function test_user_can_post_message_to_thread(): void
    {
        [$thread, $user] = $this->createThread();

        $this->assertEquals(0, Message::count());

        $response = $this->actingAs($user)->post(route('dashboard.messages.store', $thread), [
            'body' => 'Halo owner, saya sudah membooking kost ini.',
        ]);

        $response->assertRedirect();

        // Assert exactly one record is created
        $this->assertEquals(1, Message::count());
        $this->assertDatabaseHas('messages', [
            'message_thread_id' => $thread->id,
            'user_id' => $user->id,
            'body' => 'Halo owner, saya sudah membooking kost ini.',
        ]);
    }

    public function test_user_cannot_send_empty_message(): void
    {
        [$thread, $user] = $this->createThread();

        $response = $this->actingAs($user)->post(route('dashboard.messages.store', $thread), [
            'body' => '',
        ]);

        $response->assertSessionHasErrors('body');
        $this->assertEquals(0, Message::count());
    }

    public function test_user_cannot_access_thread_of_another_user(): void
    {
        [$thread] = $this->createThread();
        $otherUser = User::factory()->pengguna()->create();

        $response = $this->actingAs($otherUser)->get(route('dashboard.messages.show', $thread));
        $response->assertStatus(403);
    }
}

<?php

namespace Tests\Feature\User;

use App\Models\Booking;
use App\Models\Kost;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    private function createPaymentForUser(User $user, string $paymentStatus = 'unpaid'): Payment
    {
        $tenant = User::factory()->tenant()->create();
        $kost = Kost::factory()->create(['user_id' => $tenant->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);
        $price = RoomPrice::factory()->create(['room_id' => $room->id]);
        $booking = Booking::factory()->active()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'room_price_id' => $price->id,
        ]);

        return Payment::factory()->create([
            'booking_id' => $booking->id,
            'status' => $paymentStatus,
        ]);
    }

    public function test_user_can_view_payments(): void
    {
        $user = User::factory()->pengguna()->create();

        $response = $this->actingAs($user)->get(route('dashboard.payments.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('User/Payments/Index'));
    }

    public function test_user_can_upload_proof_for_unpaid_payment(): void
    {
        Storage::fake('public');
        $user = User::factory()->pengguna()->create();
        $payment = $this->createPaymentForUser($user, 'unpaid');

        $response = $this->actingAs($user)->post(route('dashboard.payments.proof', $payment), [
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payment_proofs', ['payment_id' => $payment->id]);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'pending_verification']);
    }

    public function test_user_cannot_upload_proof_for_paid_payment(): void
    {
        Storage::fake('public');
        $user = User::factory()->pengguna()->create();
        $payment = $this->createPaymentForUser($user, 'paid');

        $response = $this->actingAs($user)->post(route('dashboard.payments.proof', $payment), [
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_see_other_users_payment(): void
    {
        $user = User::factory()->pengguna()->create();
        $otherUser = User::factory()->pengguna()->create();
        $payment = $this->createPaymentForUser($otherUser, 'unpaid');

        $response = $this->actingAs($user)->get(route('dashboard.payments.show', $payment));
        $response->assertStatus(403);
    }
}

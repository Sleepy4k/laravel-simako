<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $bookings = $request->user()
            ->bookings()
            ->with('room:id,name,kost_id', 'room.kost:id,name,slug,thumbnail', 'roomPrice:id,period,price')
            ->latest()
            ->paginate(10);

        return Inertia::render('User/Bookings/Index', ['bookings' => $bookings]);
    }

    public function create(Room $room): Response
    {
        $room->load('kost:id,name,slug,thumbnail,status,user_id', 'kost.address', 'kost.tenant.bankAccounts', 'prices', 'facilities:id,name,icon');

        abort_if(! $room->is_available, 422, 'Kamar tidak tersedia.');
        abort_if($room->kost->status !== 'active', 422, 'Kost tidak aktif.');

        return Inertia::render('User/Bookings/Create', [
            'room' => $room,
            'kost' => $room->kost,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'room_price_id' => ['required', 'integer', 'exists:room_prices,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $room = Room::findOrFail($validated['room_id']);

        abort_if(! $room->is_available || $room->kost->status !== 'active', 422, 'Kamar tidak tersedia.');

        Booking::create([
            'user_id' => $request->user()->id,
            'room_id' => $validated['room_id'],
            'room_price_id' => $validated['room_price_id'],
            'start_date' => $validated['start_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('dashboard.bookings.index')
            ->with('success', 'Permohonan booking berhasil dikirim. Tunggu konfirmasi dari pemilik kost.');
    }

    public function show(Request $request, Booking $booking): Response
    {
        $this->authorize('view', $booking);

        $booking->load([
            'room:id,name,kost_id,floor,size_sqm',
            'room.kost:id,name,slug,thumbnail',
            'room.kost.address',
            'room.kost.tenant.userProfile:user_id,name',
            'room.kost.tenant.bankAccounts',
            'roomPrice:id,period,price,deposit',
            'payments.proofs',
            'messageThread',
            'review',
        ]);

        return Inertia::render('User/Bookings/Show', ['booking' => $booking]);
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        abort_if(! in_array($booking->status, ['pending', 'approved']), 422, 'Booking tidak dapat dibatalkan.');

        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason,
        ]);

        if ($booking->status === 'approved') {
            DB::table('rooms')->where('id', $booking->room_id)->update(['is_available' => true]);
        }

        return back()->with('success', 'Booking berhasil dibatalkan.');
    }
}

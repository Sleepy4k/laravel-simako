<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $bookings = Booking::with(
            'user.userProfile:user_id,name',
            'room.kost:id,name',
            'roomPrice:id,period,price',
        )
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only('status'),
        ]);
    }

    public function show(Booking $booking): Response
    {
        $booking->load('user.userProfile', 'room.kost:id,name,slug', 'roomPrice', 'payments.proofs');

        return Inertia::render('Admin/Bookings/Show', ['booking' => $booking]);
    }
}

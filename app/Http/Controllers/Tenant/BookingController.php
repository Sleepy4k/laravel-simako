<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\MessageThread;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $kostIds = $request->user()->kosts()->pluck('id');

        $bookings = Booking::whereHas('room.kost', fn ($q) => $q->whereIn('id', $kostIds))
            ->with('user.userProfile:user_id,name,avatar', 'room:id,name,kost_id', 'room.kost:id,name', 'roomPrice:id,period,price')
            ->latest()
            ->paginate(15);

        return Inertia::render('Tenant/Bookings/Index', ['bookings' => $bookings]);
    }

    public function show(Request $request, Booking $booking): Response
    {
        $this->authorize('view', $booking);

        $booking->load([
            'user.userProfile',
            'room.kost:id,name,slug',
            'roomPrice',
            'payments.proofs',
            'messageThread',
        ]);

        return Inertia::render('Tenant/Bookings/Show', ['booking' => $booking]);
    }

    public function approve(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('approve', $booking);

        abort_if($booking->status !== 'pending', 422, 'Booking tidak dalam status pending.');

        DB::transaction(function () use ($booking) {
            $booking->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            $booking->room->update(['is_available' => false]);
            $booking->room->kost->decrement('available_rooms');

            MessageThread::firstOrCreate(['booking_id' => $booking->id]);

            $price = $booking->roomPrice;
            $months = match ($price->period) {
                'quarterly' => 3,
                'semi_annual' => 6,
                'annual' => 12,
                default => 1,
            };

            $periodStart = Carbon::parse($booking->start_date);
            $periodEnd = $periodStart->copy()->addMonths($months)->subDay();

            Payment::create([
                'booking_id' => $booking->id,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'amount' => $price->price,
                'status' => 'unpaid',
                'due_date' => $periodStart->copy()->addDays(3),
            ]);
        });

        return back()->with('success', 'Booking berhasil disetujui. Tagihan pertama telah dibuat.');
    }

    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('approve', $booking);

        abort_if($booking->status !== 'pending', 422, 'Booking tidak dalam status pending.');

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason ?? 'Ditolak oleh pemilik kost.',
        ]);

        return back()->with('success', 'Booking berhasil ditolak.');
    }
}

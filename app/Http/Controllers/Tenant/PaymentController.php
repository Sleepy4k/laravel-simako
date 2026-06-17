<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $kostIds = $request->user()->kosts()->pluck('id');

        $payments = Payment::whereHas('booking.room.kost', fn ($q) => $q->whereIn('id', $kostIds))
            ->with('booking.user.userProfile:user_id,name', 'booking.room.kost:id,name', 'proofs')
            ->latest()
            ->paginate(15);

        return Inertia::render('Tenant/Payments/Index', ['payments' => $payments]);
    }

    public function show(Request $request, Payment $payment): Response
    {
        $this->authorize('view', $payment);

        $payment->load([
            'booking.user.userProfile',
            'booking.room.kost:id,name,slug',
            'booking.roomPrice',
            'proofs',
        ]);

        return Inertia::render('Tenant/Payments/Show', ['payment' => $payment]);
    }

    public function approve(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('approve', $payment);

        abort_if($payment->status !== 'pending_verification', 422, 'Tidak ada bukti pembayaran yang menunggu konfirmasi.');

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $booking = $payment->booking;
        if ($booking->status === 'approved') {
            $booking->update(['status' => 'active']);
        }

        return back()->with('success', 'Pembayaran berhasil dikonfirmasi.');
    }

    public function decline(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('approve', $payment);

        abort_if($payment->status !== 'pending_verification', 422, 'Tidak ada bukti pembayaran yang menunggu konfirmasi.');

        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $payment->update([
            'status' => 'declined',
            'declined_at' => now(),
            'decline_notes' => $request->notes,
        ]);

        return back()->with('success', 'Bukti pembayaran ditolak.');
    }
}

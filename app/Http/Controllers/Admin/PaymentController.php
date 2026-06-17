<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $payments = Payment::with(
            'booking.user.userProfile:user_id,name',
            'booking.room.kost:id,name',
        )
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'filters' => $request->only('status'),
        ]);
    }

    public function show(Payment $payment): Response
    {
        $payment->load('booking.user.userProfile', 'booking.room.kost:id,name,slug', 'proofs');

        return Inertia::render('Admin/Payments/Show', ['payment' => $payment]);
    }
}

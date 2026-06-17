<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentProof;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $payments = Payment::whereHas('booking', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with('booking.room.kost:id,name,slug')
            ->latest()
            ->paginate(10);

        return Inertia::render('User/Payments/Index', ['payments' => $payments]);
    }

    public function show(Request $request, Payment $payment): Response
    {
        $this->authorize('view', $payment);

        $payment->load([
            'booking.room.kost:id,name,slug',
            'booking.room.kost.tenant.bankAccounts',
            'proofs',
        ]);

        return Inertia::render('User/Payments/Show', ['payment' => $payment]);
    }

    public function uploadProof(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('uploadProof', $payment);

        abort_if($payment->status === 'paid', 422, 'Pembayaran sudah dikonfirmasi.');

        $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $path = $request->file('proof')->store('payment-proofs', 'public');

        PaymentProof::create([
            'payment_id' => $payment->id,
            'path' => $path,
            'uploaded_at' => now(),
        ]);

        $payment->update(['status' => 'pending_verification']);

        return back()->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu konfirmasi dari pemilik kost.');
    }
}

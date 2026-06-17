<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EarningsController extends Controller
{
    public function index(Request $request): Response
    {
        $kostIds = $request->user()->kosts()->pluck('id');

        $totalEarnings = Payment::whereHas('booking.room.kost', fn ($q) => $q->whereIn('id', $kostIds))
            ->where('status', 'paid')
            ->sum('amount');

        $monthlyEarnings = Payment::whereHas('booking.room.kost', fn ($q) => $q->whereIn('id', $kostIds))
            ->where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $recentPayments = Payment::whereHas('booking.room.kost', fn ($q) => $q->whereIn('id', $kostIds))
            ->where('status', 'paid')
            ->with('booking.user.userProfile:user_id,name', 'booking.room.kost:id,name')
            ->latest('paid_at')
            ->limit(10)
            ->get();

        return Inertia::render('Tenant/Earnings/Index', [
            'totalEarnings' => $totalEarnings,
            'monthlyEarnings' => $monthlyEarnings,
            'recentPayments' => $recentPayments,
        ]);
    }
}

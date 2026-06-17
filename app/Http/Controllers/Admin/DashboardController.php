<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kost;
use App\Models\Payment;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => [
                'total_users' => User::whereHas('role', fn ($q) => $q->where('name', 'pengguna'))->count(),
                'total_tenants' => User::whereHas('role', fn ($q) => $q->where('name', 'tenant'))->count(),
                'total_kosts' => Kost::count(),
                'active_kosts' => Kost::where('status', 'active')->count(),
                'total_bookings' => Booking::count(),
                'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
                'pending_verifications' => User::whereHas('role', fn ($q) => $q->where('name', 'tenant'))
                    ->whereHas('tenantProfile', fn ($q) => $q->whereNull('verified_at'))
                    ->count(),
            ],
        ]);
    }
}

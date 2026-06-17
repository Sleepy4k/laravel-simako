<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kost;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user()->load('role', 'userProfile:user_id,name,avatar', 'tenantProfile');

        $props = match ($user->role->name) {
            'admin' => $this->adminProps(),
            'tenant' => $this->tenantProps($user),
            default => $this->penggunaProps($user),
        };

        return Inertia::render('Dashboard/Index', array_merge(['role' => $user->role->name], $props));
    }

    /**
     * @return array<string, mixed>
     */
    private function penggunaProps(User $user): array
    {
        return [
            'stats' => [
                'total_bookings' => $user->bookings()->count(),
                'active_bookings' => $user->bookings()->where('status', 'active')->count(),
                'pending_payments' => Payment::whereHas('booking', fn ($q) => $q->where('user_id', $user->id))
                    ->whereIn('status', ['unpaid', 'pending_verification'])
                    ->count(),
            ],
            'recent_bookings' => $user->bookings()
                ->with('room.kost:id,name,slug,thumbnail', 'roomPrice:id,period,price')
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function tenantProps(User $user): array
    {
        $kostIds = $user->kosts()->pluck('id');

        return [
            'stats' => [
                'total_kosts' => $kostIds->count(),
                'active_kosts' => $user->kosts()->where('status', 'active')->count(),
                'pending_bookings' => Booking::whereIn('room_id', function ($q) use ($kostIds) {
                    $q->select('id')->from('rooms')->whereIn('kost_id', $kostIds);
                })->where('status', 'pending')->count(),
                'pending_payments' => Payment::whereHas('booking.room.kost', fn ($q) => $q->whereIn('id', $kostIds))
                    ->where('status', 'pending_verification')
                    ->count(),
            ],
            'recent_bookings' => Booking::whereHas('room.kost', fn ($q) => $q->whereIn('id', $kostIds))
                ->with('user.userProfile:user_id,name,avatar', 'room:id,name,kost_id', 'room.kost:id,name')
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function adminProps(): array
    {
        return [
            'stats' => [
                'total_users' => User::whereHas('role', fn ($q) => $q->where('name', 'pengguna'))->count(),
                'total_tenants' => User::whereHas('role', fn ($q) => $q->where('name', 'tenant'))->count(),
                'total_kosts' => Kost::count(),
                'total_bookings' => Booking::count(),
                'pending_tenant_verifications' => User::whereHas('role', fn ($q) => $q->where('name', 'tenant'))
                    ->whereHas('tenantProfile', fn ($q) => $q->whereNull('verified_at'))
                    ->count(),
            ],
        ];
    }
}

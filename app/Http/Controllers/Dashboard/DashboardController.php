<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kost;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $expensesQuery = Payment::whereHas('booking', fn ($q) => $q->where('user_id', $user->id))
            ->where('status', 'paid');

        return [
            'stats' => [
                'active_bookings' => $user->bookings()->where('status', 'active')->count(),
                'pending_payments' => Payment::whereHas('booking', fn ($q) => $q->where('user_id', $user->id))
                    ->whereIn('status', ['unpaid', 'pending_verification'])
                    ->count(),
            ],
            'recentBookings' => $user->bookings()
                ->with('room.kost:id,name,slug,thumbnail', 'roomPrice:id,period,price')
                ->latest()
                ->limit(5)
                ->get(),
            'chartData' => $this->getMonthlyChartData($expensesQuery),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function tenantProps(User $user): array
    {
        $kostIds = $user->kosts()->pluck('id');

        $activeBookingsCount = Booking::whereIn('room_id', function ($q) use ($kostIds) {
            $q->select('id')->from('rooms')->whereIn('kost_id', $kostIds);
        })->where('status', 'active')->count();

        $pendingVerificationsCount = Payment::whereHas('booking.room', fn ($q) => $q->whereIn('kost_id', $kostIds))
            ->where('status', 'pending_verification')
            ->count();

        $monthlyEarnings = Payment::whereHas('booking.room', fn ($q) => $q->whereIn('kost_id', $kostIds))
            ->where('status', 'paid')
            ->where('paid_at', '>=', now()->startOfMonth())
            ->sum('amount');

        $earningsQuery = Payment::whereHas('booking.room', fn ($q) => $q->whereIn('kost_id', $kostIds))
            ->where('status', 'paid');

        return [
            'stats' => [
                'total_kosts' => $kostIds->count(),
                'active_bookings' => $activeBookingsCount,
                'pending_verifications' => $pendingVerificationsCount,
                'monthly_earnings' => (int) $monthlyEarnings,
            ],
            'recentBookings' => Booking::whereHas('room.kost', fn ($q) => $q->whereIn('id', $kostIds))
                ->with('user.userProfile:user_id,name,avatar', 'room:id,name,kost_id', 'room.kost:id,name')
                ->latest()
                ->limit(5)
                ->get(),
            'chartData' => $this->getMonthlyChartData($earningsQuery),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function adminProps(): array
    {
        $totalRevenue = Payment::where('status', 'paid')->sum('amount');
        $pendingVerificationsCount = Payment::where('status', 'pending_verification')->count();
        $adminEarningsQuery = Payment::where('status', 'paid');

        return [
            'stats' => [
                'total_users' => User::whereHas('role', fn ($q) => $q->where('name', 'pengguna'))->count(),
                'total_tenants' => User::whereHas('role', fn ($q) => $q->where('name', 'tenant'))->count(),
                'active_kosts' => Kost::where('status', 'active')->count(),
                'total_revenue' => (int) $totalRevenue,
                'pending_verifications' => $pendingVerificationsCount,
            ],
            'chartData' => $this->getMonthlyChartData($adminEarningsQuery),
        ];
    }

    /**
     * Get monthly chart data safely for both MySQL and SQLite.
     */
    private function getMonthlyChartData(Builder $query): array
    {
        $months = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $months[$monthKey] = $date->translatedFormat('F');
            $values[$monthKey] = 0;
        }

        $dbDriver = DB::connection()->getDriverName();
        $selectRaw = $dbDriver === 'sqlite'
            ? "strftime('%Y-%m', paid_at) as month, SUM(amount) as total"
            : "DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(amount) as total";

        $results = $query
            ->selectRaw($selectRaw)
            ->where('paid_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->get();

        foreach ($results as $row) {
            if (isset($values[$row->month])) {
                $values[$row->month] = (int) $row->total;
            }
        }

        return [
            'labels' => array_values($months),
            'values' => array_values($values),
        ];
    }
}

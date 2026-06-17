<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(Request $request): Response
    {
        $tenants = User::whereHas('role', fn ($q) => $q->where('name', 'tenant'))
            ->with('tenantProfile', 'userProfile:user_id,name,avatar')
            ->withCount('kosts')
            ->when($request->search, fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('email', 'like', '%'.$request->search.'%')
                    ->orWhere('phone', 'like', '%'.$request->search.'%')
                    ->orWhereHas('tenantProfile', fn ($q3) => $q3->where('business_name', 'like', '%'.$request->search.'%'));
            }))
            ->when($request->status === 'unverified', fn ($q) => $q->whereHas('tenantProfile', fn ($q2) => $q2->whereNull('verified_at')))
            ->when($request->status === 'verified', fn ($q) => $q->whereHas('tenantProfile', fn ($q2) => $q2->whereNotNull('verified_at')))
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Tenants/Index', [
            'tenants' => $tenants,
            'filters' => $request->only('search', 'status'),
        ]);
    }

    public function show(User $user): Response
    {
        $user->load('tenantProfile.verifier.userProfile:user_id,name', 'bankAccounts', 'kosts.address');

        return Inertia::render('Admin/Tenants/Show', ['tenant' => $user]);
    }

    public function verify(Request $request, User $user): RedirectResponse
    {
        abort_if(! $user->isTenant(), 404);

        $user->tenantProfile()->update([
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        return back()->with('success', 'Tenant berhasil diverifikasi.');
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        abort_if(! $user->isTenant(), 404);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user->tenantProfile()->update([
            'suspended_at' => now(),
            'suspension_reason' => $request->reason,
        ]);

        $user->update(['is_active' => false]);

        return back()->with('success', 'Tenant berhasil disuspend.');
    }
}

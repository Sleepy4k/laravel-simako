<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::whereHas('role', fn ($q) => $q->where('name', 'pengguna'))
            ->with('userProfile:user_id,name,avatar,gender')
            ->when($request->search, fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('email', 'like', '%'.$request->search.'%')
                    ->orWhere('phone', 'like', '%'.$request->search.'%')
                    ->orWhereHas('userProfile', fn ($q3) => $q3->where('name', 'like', '%'.$request->search.'%'));
            }))
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only('search'),
        ]);
    }

    public function show(User $user): Response
    {
        $user->load('userProfile', 'bookings.room.kost:id,name');

        return Inertia::render('Admin/Users/Show', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Status pengguna berhasil diperbarui.');
    }
}

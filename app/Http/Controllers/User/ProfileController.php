<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        $userProfile = $user->userProfile()->first();

        return Inertia::render('User/Profile/Show', [
            'user' => $user,
            'userProfile' => $userProfile,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone,'.$request->user()->id],
            'email' => ['nullable', 'string', 'email', 'unique:users,email,'.$request->user()->id],
            'gender' => ['nullable', 'in:male,female,other'],
            'birth_date' => ['nullable', 'date'],
            'id_card_number' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'id_card_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = $request->user();

        $user->update([
            'email' => $validated['email'] ?? $user->email,
            'phone' => $validated['phone'] ?? $user->phone,
        ]);

        $profileData = [
            'name' => $validated['name'],
            'gender' => $validated['gender'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'id_card_number' => $validated['id_card_number'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            $profileData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->hasFile('id_card_image')) {
            $profileData['id_card_image'] = $request->file('id_card_image')->store('id-cards', 'public');
        }

        $user->userProfile()->updateOrCreate(['user_id' => $user->id], $profileData);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}

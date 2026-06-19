<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterTenantRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\BankAccount;
use App\Models\Role;
use App\Models\TenantProfile;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function showChoice(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function showUserForm(): Response
    {
        return Inertia::render('Auth/RegisterUser');
    }

    public function storeUser(RegisterUserRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($request, $validated) {
            $penggunaRole = Role::where('name', 'pengguna')->first();

            $user = User::create([
                'role_id' => $penggunaRole->id,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'],
            ]);

            $idCardImagePath = null;
            if ($request->hasFile('id_card_image')) {
                $idCardImagePath = $request->file('id_card_image')->store('id-cards', 'public');
            }

            UserProfile::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'id_card_number' => $validated['id_card_number'] ?? null,
                'id_card_image' => $idCardImagePath,
                'gender' => $validated['gender'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
            ]);

            return $user;
        });

        Auth::login($user);

        return redirect()->route('dashboard.index');
    }

    public function showTenantForm(): Response
    {
        return Inertia::render('Auth/RegisterTenant');
    }

    public function storeTenant(RegisterTenantRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($request, $validated) {
            $tenantRole = Role::where('name', 'tenant')->first();

            $user = User::create([
                'role_id' => $tenantRole->id,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'],
            ]);

            $identityImagePath = null;
            if ($request->hasFile('identity_image')) {
                $identityImagePath = $request->file('identity_image')->store('identity-images', 'public');
            }

            TenantProfile::create([
                'user_id' => $user->id,
                'business_name' => $validated['business_name'],
                'identity_image' => $identityImagePath,
            ]);

            BankAccount::create([
                'user_id' => $user->id,
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_holder' => $validated['account_holder'],
                'is_primary' => true,
            ]);

            return $user;
        });

        Auth::login($user);

        return redirect()->route('dashboard.index');
    }
}

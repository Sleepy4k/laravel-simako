<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function showForm(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request): \Illuminate\Http\RedirectResponse
    {
        $credentials = [
            'login' => $request->login,
            'password' => $request->password,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'login' => 'Email/nomor HP atau password salah.',
            ])->onlyInput('login');
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard.index');
    }

    public function destroy(): \Illuminate\Http\RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    }
}

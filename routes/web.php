<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\KostController as PublicKostController;
use App\Http\Controllers\Tenant;
use App\Http\Controllers\User;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/help', [HomeController::class, 'help'])->name('help');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/kosts', [PublicKostController::class, 'index'])->name('kosts.index');
Route::get('/kosts/{kost:slug}', [PublicKostController::class, 'show'])->name('kosts.show');

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'showChoice'])->name('register');
    Route::get('/register/user', [RegisterController::class, 'showUserForm'])->name('register.user');
    Route::post('/register/user', [RegisterController::class, 'storeUser'])->name('register.user.store');
    Route::get('/register/tenant', [RegisterController::class, 'showTenantForm'])->name('register.tenant');
    Route::post('/register/tenant', [RegisterController::class, 'storeTenant'])->name('register.tenant.store');
});

Route::middleware('auth')->post('/logout', [LoginController::class, 'destroy'])->name('logout');

// ── Dashboard (semua role, role-gated secara internal) ────────────────────────
Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/profile', [User\ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [User\ProfileController::class, 'update'])->name('profile.update');

    // ── Pengguna ─────────────────────────────────────────────────────────────
    Route::middleware('role:pengguna')->group(function () {
        Route::get('/bookings', [User\BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create/{room}', [User\BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [User\BookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}', [User\BookingController::class, 'show'])->name('bookings.show');
        Route::patch('/bookings/{booking}/cancel', [User\BookingController::class, 'cancel'])->name('bookings.cancel');

        Route::get('/payments', [User\PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}', [User\PaymentController::class, 'show'])->name('payments.show');
        Route::post('/payments/{payment}/proof', [User\PaymentController::class, 'uploadProof'])->name('payments.proof');

        Route::get('/messages', [User\MessageController::class, 'index'])->name('messages.index');
        Route::post('/messages/kost/{kost}', [User\MessageController::class, 'storeFromKost'])->name('messages.storeFromKost');
        Route::get('/messages/{thread}', [User\MessageController::class, 'show'])->name('messages.show');
        Route::post('/messages/{thread}', [User\MessageController::class, 'store'])->name('messages.store');
    });

    // ── Tenant ───────────────────────────────────────────────────────────────
    Route::middleware('role:tenant')->group(function () {
        Route::get('/kosts', [Tenant\KostController::class, 'index'])->name('tenant.kosts.index');
        Route::get('/kosts/create', [Tenant\KostController::class, 'create'])->name('tenant.kosts.create');
        Route::post('/kosts', [Tenant\KostController::class, 'store'])->name('tenant.kosts.store');
        Route::get('/kosts/{kost}/edit', [Tenant\KostController::class, 'edit'])->name('tenant.kosts.edit');
        Route::patch('/kosts/{kost}', [Tenant\KostController::class, 'update'])->name('tenant.kosts.update');
        Route::delete('/kosts/{kost}', [Tenant\KostController::class, 'destroy'])->name('tenant.kosts.destroy');

        Route::get('/kosts/{kost}/rooms', [Tenant\RoomController::class, 'index'])->name('tenant.rooms.index');
        Route::get('/kosts/{kost}/rooms/create', [Tenant\RoomController::class, 'create'])->name('tenant.rooms.create');
        Route::post('/kosts/{kost}/rooms', [Tenant\RoomController::class, 'store'])->name('tenant.rooms.store');
        Route::get('/kosts/{kost}/rooms/{room}/edit', [Tenant\RoomController::class, 'edit'])->name('tenant.rooms.edit');
        Route::patch('/kosts/{kost}/rooms/{room}', [Tenant\RoomController::class, 'update'])->name('tenant.rooms.update');
        Route::delete('/kosts/{kost}/rooms/{room}', [Tenant\RoomController::class, 'destroy'])->name('tenant.rooms.destroy');

        Route::get('/tenant/bookings', [Tenant\BookingController::class, 'index'])->name('tenant.bookings.index');
        Route::get('/tenant/bookings/{booking}', [Tenant\BookingController::class, 'show'])->name('tenant.bookings.show');
        Route::patch('/tenant/bookings/{booking}/approve', [Tenant\BookingController::class, 'approve'])->name('tenant.bookings.approve');
        Route::patch('/tenant/bookings/{booking}/reject', [Tenant\BookingController::class, 'reject'])->name('tenant.bookings.reject');

        Route::get('/tenant/payments', [Tenant\PaymentController::class, 'index'])->name('tenant.payments.index');
        Route::get('/tenant/payments/{payment}', [Tenant\PaymentController::class, 'show'])->name('tenant.payments.show');
        Route::patch('/tenant/payments/{payment}/approve', [Tenant\PaymentController::class, 'approve'])->name('tenant.payments.approve');
        Route::patch('/tenant/payments/{payment}/decline', [Tenant\PaymentController::class, 'decline'])->name('tenant.payments.decline');

        Route::get('/tenant/messages', [Tenant\MessageController::class, 'index'])->name('tenant.messages.index');
        Route::get('/tenant/messages/{thread}', [Tenant\MessageController::class, 'show'])->name('tenant.messages.show');
        Route::post('/tenant/messages/{thread}', [Tenant\MessageController::class, 'store'])->name('tenant.messages.store');

        Route::get('/bank-accounts', [Tenant\BankAccountController::class, 'index'])->name('tenant.bank-accounts.index');
        Route::post('/bank-accounts', [Tenant\BankAccountController::class, 'store'])->name('tenant.bank-accounts.store');
        Route::patch('/bank-accounts/{bankAccount}', [Tenant\BankAccountController::class, 'update'])->name('tenant.bank-accounts.update');
        Route::delete('/bank-accounts/{bankAccount}', [Tenant\BankAccountController::class, 'destroy'])->name('tenant.bank-accounts.destroy');

        Route::get('/earnings', [Tenant\EarningsController::class, 'index'])->name('tenant.earnings.index');
    });

    // ── Admin ────────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [Admin\UserController::class, 'show'])->name('users.show');
        Route::patch('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');

        Route::get('/tenants', [Admin\TenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/{user}', [Admin\TenantController::class, 'show'])->name('tenants.show');
        Route::patch('/tenants/{user}/verify', [Admin\TenantController::class, 'verify'])->name('tenants.verify');
        Route::patch('/tenants/{user}/suspend', [Admin\TenantController::class, 'suspend'])->name('tenants.suspend');

        Route::get('/kosts', [Admin\KostController::class, 'index'])->name('kosts.index');
        Route::get('/kosts/{kost}', [Admin\KostController::class, 'show'])->name('kosts.show');

        Route::get('/bookings', [Admin\BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{booking}', [Admin\BookingController::class, 'show'])->name('bookings.show');

        Route::get('/payments', [Admin\PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}', [Admin\PaymentController::class, 'show'])->name('payments.show');
    });
});

<?php

namespace App\Providers;

use App\Auth\SimakoUserProvider;
use App\Models\Booking;
use App\Models\Kost;
use App\Models\MessageThread;
use App\Models\Payment;
use App\Models\Room;
use App\Policies\BookingPolicy;
use App\Policies\KostPolicy;
use App\Policies\MessageThreadPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\RoomPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerAuthProvider();
        $this->registerPolicies();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function registerAuthProvider(): void
    {
        Auth::provider('simako', function ($app, array $config) {
            return new SimakoUserProvider($app['hash'], $config['model']);
        });
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Kost::class, KostPolicy::class);
        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(MessageThread::class, MessageThreadPolicy::class);
    }
}

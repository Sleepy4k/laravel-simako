---
name: laravel-service-provider-binding
description: Wire Laravel's container in service providers - bind interfaces to implementations, use singleton vs bind vs scoped correctly, and defer providers - instead of newing dependencies in code.
related:
  - laravel-project-structure
  - laravel-octane-state-safety
  - laravel-basic-code
  - laravel-best-practices
---

# Laravel Service Provider Binding
- Interfaces are bound to concrete classes in a provider's `register()`, so callers depend on abstractions.
- Lifetime is chosen deliberately: `bind` (new each resolve), `singleton` (one per app), `scoped` (one per request/job).
- `register()` only binds; bootstrapping work (events, routes, publishing) happens in `boot()`.

## Safety contract: non-negotiable
- Abort if `register()` resolves another service (`$this->app->make(...)`) — dependencies may not be bound yet.
- Abort if a request-scoped service is bound as `singleton` under Octane (stale data across requests — see [[laravel-octane-state-safety]]).
- Abort if business code `new`s a dependency that has an interface binding (defeats swapping/mocking).
- Abort if a heavy provider isn't deferred when it only binds services (slows every request boot).

## Required tools
- Laravel >= 11, PHP >= 8.2.

## Gotchas
- `singleton` caches the first instance forever; under Octane that means across requests — use `scoped` for per-request state.
- Contextual binding (`when(...)->needs(...)->give(...)`) injects different implementations per consumer.
- A `DeferrableProvider` must list `provides()` accurately or its bindings won't be found.
- `boot()` runs after all providers registered, so it's safe to resolve there; `register()` is not.

## Workflow
1. Define an interface (e.g. `PaymentGateway`) and a concrete (`StripeGateway`).
2. In a provider's `register()`, `bind`/`singleton`/`scoped` the interface to the concrete.
3. Type-hint the interface in consumers; the container injects the bound implementation.
4. Pick the lifetime against state and runtime (Octane → prefer `scoped` for request data).

## Code Examples (Good vs Bad)

### Bad Example 1 (newing a concrete, no abstraction)
```php
class CheckoutService {
    public function pay(Order $o) {
        $gateway = new StripeGateway(config('services.stripe.key')); // can't mock or swap
        return $gateway->charge($o->total);
    }
}
```

### Bad Example 2 (resolving inside register, wrong lifetime)
```php
public function register(): void {
    $logger = $this->app->make(Logger::class);            // may be unbound here
    $this->app->singleton(CurrentTenant::class, fn () =>  // per-request data as singleton
        new CurrentTenant(request()->user()->tenant_id)); // stale under Octane
}
```

### Bad Example 3 (bootstrapping work in register())
```php
public function register(): void {
    Route::middleware('api')->group(base_path('routes/api.php')); // routes/events belong in boot(), not register()
}
```

### Bad Example 4 (heavy provider not deferred)
```php
class ReportingServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->singleton(PdfEngine::class, fn () => new PdfEngine(/* loads fonts, heavy */));
    } // runs on every request boot even when no report is generated
}
```

### Bad Example 5 (DeferrableProvider with wrong provides())
```php
class PaymentProvider extends ServiceProvider implements DeferrableProvider {
    public function register(): void { $this->app->bind(PaymentGateway::class, StripeGateway::class); }
    public function provides(): array { return []; } // empty -> the deferred binding is never found
}
```

### Good Example 1 (interface bound to implementation)
```php
// app/Providers/AppServiceProvider.php
public function register(): void {
    $this->app->bind(PaymentGateway::class, fn ($app) =>
        new StripeGateway(config('services.stripe.key')));
}
// consumer depends on the abstraction
class CheckoutService {
    public function __construct(private readonly PaymentGateway $gateway) {}
}
```

### Good Example 2 (scoped binding + contextual binding)
```php
public function register(): void {
    $this->app->scoped(CurrentTenant::class, fn () =>     // fresh per request/job
        new CurrentTenant(request()->user()?->tenant_id));

    $this->app->when(ReportController::class)
        ->needs(PaymentGateway::class)
        ->give(SandboxGateway::class);                    // different impl for one consumer
}
```

### Good Example 3 (bootstrapping lives in boot())
```php
public function boot(): void {
    Route::middleware('api')->group(base_path('routes/api.php')); // safe: all providers already registered
}
```

### Good Example 4 (deferred provider with correct provides())
```php
class PaymentProvider extends ServiceProvider implements DeferrableProvider {
    public function register(): void { $this->app->bind(PaymentGateway::class, StripeGateway::class); }
    public function provides(): array { return [PaymentGateway::class]; } // bound lazily on first resolve
}
```

### Good Example 5 (singleton for a stateless shared service)
```php
$this->app->singleton(MarkdownRenderer::class); // no per-request state -> one instance is correct and cheap
```

## Related skills
- [[laravel-project-structure]] — services/repositories these bindings wire together.
- [[laravel-octane-state-safety]] — why lifetime choice matters under persistent workers.
- [[laravel-basic-code]] — constructor injection at the call site.
- [[laravel-best-practices]] — depend on abstractions as a standing rule.

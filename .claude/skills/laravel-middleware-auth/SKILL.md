---
name: laravel-middleware-auth
description: Guard Laravel routes with middleware - auth/auth:sanctum, can: authorization, throttle, and custom middleware ordering - instead of repeating auth checks inside every controller.
related:
  - laravel-sanctum-api-tokens
  - laravel-policy-gate-auth
  - laravel-security-hardening
  - laravel-project-structure
---

# Laravel Middleware Auth
- Authentication, authorization, and rate limiting are applied as route middleware, not duplicated in controllers.
- `auth`/`auth:sanctum` rejects unauthenticated requests before the controller runs.
- `can:` and `throttle:` middleware gate authorization and abuse at the edge.

## Safety contract: non-negotiable
- Abort if a protected route relies on an in-controller `if (auth()->check())` instead of route middleware (easy to forget on a new action).
- Abort if a custom middleware that should stop the request returns `$next($request)` on the failure path (it lets the request through).
- Abort if middleware order puts an expensive/authorizing step before authentication (work done for anonymous users).
- Abort if a public route accidentally inherits an `auth` group, or an admin route omits the authorization middleware.

## Required tools
- Laravel >= 11, PHP >= 8.2; `bootstrap/app.php` for global/aliased middleware registration.

## Gotchas
- In Laravel 11 middleware is registered in `bootstrap/app.php` (`->withMiddleware(...)`), not `Http/Kernel.php`.
- Middleware runs in the order listed; `auth` must precede `can:` or the authorization check has no user.
- A custom middleware must call `$next($request)` to continue and `return` a response/`abort()` to stop — forgetting `return` continues the chain.
- `throttle:api` uses the `api` rate limiter defined in a service provider; undefined limiters silently do nothing.

## Workflow
1. Group protected routes under `Route::middleware(['auth:sanctum'])`.
2. Add `can:update,post` (or a Policy via `->can(...)`) for per-action authorization.
3. Apply `throttle:60,1` (or a named limiter) to abuse-prone endpoints.
4. For custom checks, `make:middleware`, register an alias in `bootstrap/app.php`, and always `return`.

## Code Examples (Good vs Bad)

### Bad Example 1 (auth logic scattered in controllers)
```php
public function destroy(Post $post) {
    if (!auth()->check()) { abort(401); }   // repeated in every action; easy to omit on the next
    $post->delete();
}
```

### Bad Example 2 (custom middleware that never blocks)
```php
public function handle(Request $request, Closure $next) {
    if (! $request->user()?->is_admin) {
        response('Forbidden', 403);          // built but not returned -> request continues!
    }
    return $next($request);
}
```

### Bad Example 3 (authorization before authentication)
```php
Route::get('/reports', [ReportController::class, 'index'])
    ->middleware(['can:viewReports', 'auth']); // can: runs first with no user -> error or wrong decision
```

### Bad Example 4 (admin route missing the authz middleware)
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/users/{user}', [AdminController::class, 'destroy']); // any logged-in user can delete users
});
```

### Bad Example 5 (throttle names an undefined limiter)
```php
Route::post('/otp', OtpController::class)->middleware('throttle:otp'); // no RateLimiter::for('otp', ...) -> no limit at all
```

### Good Example 1 (middleware groups guard the routes)
```php
// routes/api.php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])
        ->middleware('can:delete,post');     // authz at the edge, no controller boilerplate
});
```

### Good Example 2 (correct custom middleware + registration)
```php
// app/Http/Middleware/EnsureAdmin.php
public function handle(Request $request, Closure $next): Response {
    abort_unless($request->user()?->is_admin, 403); // stops here on failure
    return $next($request);
}
// bootstrap/app.php
->withMiddleware(fn ($m) => $m->alias(['admin' => EnsureAdmin::class]))
```

### Good Example 3 (authenticate, then authorize)
```php
Route::get('/reports', [ReportController::class, 'index'])
    ->middleware(['auth:sanctum', 'can:viewReports']); // user established before the authz check
```

### Good Example 4 (named rate limiter defined and applied)
```php
// AppServiceProvider::boot()
RateLimiter::for('otp', fn (Request $r) => Limit::perMinute(5)->by($r->ip()));
// routes/api.php
Route::post('/otp', OtpController::class)->middleware('throttle:otp'); // 5/min enforced
```

### Good Example 5 (admin group carries the authz middleware)
```php
Route::middleware(['auth:sanctum', 'can:manage-users'])->group(function () {
    Route::delete('/users/{user}', [AdminController::class, 'destroy']); // only authorized managers
});
```

## Related skills
- [[laravel-sanctum-api-tokens]] — what `auth:sanctum` authenticates against.
- [[laravel-policy-gate-auth]] — the Policies that `can:` middleware invokes.
- [[laravel-security-hardening]] — rate limiting and auth as defense layers.
- [[laravel-project-structure]] — middleware as the HTTP-layer boundary.

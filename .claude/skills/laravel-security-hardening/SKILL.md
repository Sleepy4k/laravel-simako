---
name: laravel-security-hardening
description: Harden Laravel against the OWASP top risks - mass-assignment guards, parameter-bound queries, Blade auto-escaping, CSRF, signed URLs, and no debug/secrets in production - across the app.
related:
  - laravel-policy-gate-auth
  - laravel-form-requests-advanced
  - laravel-sanctum-api-tokens
  - laravel-middleware-auth
---

# Laravel Security Hardening
- Input is validated (Form Requests) and writes go through `$fillable` guards — no blind mass-assignment.
- Queries use Eloquent/bindings; Blade `{{ }}` auto-escapes; `{!! !!}` is reserved for trusted, sanitized HTML.
- Production runs with `APP_DEBUG=false`, secrets only in `.env`, CSRF on web forms, and signed URLs for tokenized links.

## Safety contract: non-negotiable
- Abort if request data is mass-assigned to a model without `$fillable` (privilege fields like `is_admin` can be set).
- Abort if `DB::raw`/`whereRaw` interpolates request input instead of using bindings (SQL injection).
- Abort if untrusted content is rendered with `{!! !!}` without sanitization (stored XSS).
- Abort if `APP_DEBUG=true`, `.env` is committed, or secrets are hardcoded in source (information disclosure).

## Required tools
- Laravel >= 11, PHP >= 8.2; optionally `mews/purifier` for HTML sanitization, Larastan in CI.

## Gotchas
- `Model::create($request->all())` is mass-assignable; even with `$fillable`, prefer `$request->validated()`.
- Blade `{{ $x }}` escapes HTML but not JS context — never inject user data straight into a `<script>` block.
- CSRF protection applies to the `web` middleware group; stateless API routes use token auth instead, not the CSRF cookie.
- `URL::signedRoute(...)` links are tamper-evident only if you validate them with the `signed` middleware.

## Workflow
1. Declare `$fillable` on every model filled from input; pass `validated()` (see [[laravel-form-requests-advanced]]).
2. Use the query builder/bindings everywhere; audit `whereRaw`/`DB::raw` for interpolation.
3. Keep Blade output escaped; sanitize any `{!! !!}` HTML; set CSP headers via middleware.
4. Set `APP_DEBUG=false` and rotate any leaked secret; verify `.env` is gitignored.

## Code Examples (Good vs Bad)

### Bad Example 1 (mass-assignment + SQL injection)
```php
$user = User::create($request->all());            // can set is_admin => true
$rows = DB::select("select * from posts where title like '%{$request->q}%'"); // injection
```

### Bad Example 2 (XSS + debug/secrets leak)
```blade
{!! $comment->body !!}   {{-- raw untrusted HTML -> stored XSS --}}
```
```php
// .env on production
APP_DEBUG=true            // full stack traces + config to any visitor on error
$key = 'sk_live_abc123';  // hardcoded secret in source
```

### Bad Example 3 (whereRaw interpolates input)
```php
$sort = $request->sort;
$users = User::whereRaw("status = '{$request->status}'")->orderByRaw($sort)->get(); // injection in both
```

### Bad Example 4 (open redirect from user input)
```php
public function login(Request $request) {
    return redirect($request->input('next')); // next=//evil.com -> phishing redirect off-site
}
```

### Bad Example 5 (signed route never validated)
```php
Route::get('/unsubscribe/{user}', UnsubscribeController::class); // no `signed` middleware
// the URL was signed when generated, but nothing verifies the signature -> anyone can forge it
```

### Good Example 1 (guarded write + bound query)
```php
$user = User::create($request->safe()->only(['name', 'email'])); // explicit allowlist
$rows = Post::where('title', 'like', '%'.$request->validated()['q'].'%')->get(); // bound
```

### Good Example 2 (escaped output, signed URL, no debug)
```blade
{{ $comment->body }}      {{-- auto-escaped --}}
{!! clean($comment->html) !!}  {{-- only after sanitizing trusted HTML --}}
```
```php
$url = URL::signedRoute('unsubscribe', ['user' => $user->id]); // tamper-evident
Route::get('/unsubscribe/{user}', ...)->middleware('signed');
// .env (prod): APP_DEBUG=false ; secrets read via config('services.stripe.key')
```

### Good Example 3 (bound query + allowlisted sort)
```php
$sort = in_array($request->sort, ['name', 'created_at'], true) ? $request->sort : 'created_at';
$users = User::where('status', $request->validated()['status'])->orderBy($sort)->get(); // bound + allowlisted
```

### Good Example 4 (redirect only to internal paths)
```php
public function login(Request $request) {
    $next = $request->input('next', '/dashboard');
    return redirect(str_starts_with($next, '/') ? $next : '/dashboard'); // never off-site
}
```

### Good Example 5 (signature enforced by middleware)
```php
Route::get('/unsubscribe/{user}', UnsubscribeController::class)->middleware('signed'); // 403 on a tampered URL
```

## Related skills
- [[laravel-policy-gate-auth]] — object-level authorization (broken access control).
- [[laravel-form-requests-advanced]] — validation as the first input defense.
- [[laravel-sanctum-api-tokens]] — credential handling for APIs.
- [[laravel-middleware-auth]] — auth, throttling, and signed-URL middleware.

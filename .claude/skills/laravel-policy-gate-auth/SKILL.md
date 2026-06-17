---
name: laravel-policy-gate-auth
description: Authorize actions in Laravel with Policies and Gates - centralized rules, authorizeResource, and Gate::authorize - instead of scattering owner-id checks across controllers.
related:
  - laravel-sanctum-api-tokens
  - laravel-middleware-auth
  - laravel-security-hardening
  - laravel-form-requests-advanced
---

# Laravel Policy & Gate Authorization
- Per-model rules live in a Policy class (`view`, `create`, `update`, `delete`); ad-hoc rules use a Gate.
- Controllers call `$this->authorize(...)` / `Gate::authorize(...)`; a failed check throws `AuthorizationException` (403).
- Authorization is enforced on every mutating endpoint, not just hidden in the UI.

## Safety contract: non-negotiable
- Abort if an update/delete action runs without an explicit Policy/Gate check (broken object-level authorization = IDOR).
- Abort if ownership is compared by hand (`$user->id === $model->user_id`) in a controller instead of a Policy method.
- Abort if a Policy is defined but never registered/auto-discovered, so `authorize` silently passes.
- Abort if authorization decisions depend on request input the client controls (e.g. a `role` field in the body).

## Required tools
- Laravel >= 11 (auto-discovered policies), PHP >= 8.2.

## Gotchas
- Returning `null` from a Policy method means "no opinion" — for a deny you must return `false`; `before()` returning `true` bypasses all checks.
- `authorizeResource()` in a controller constructor maps RESTful methods to Policy methods automatically — but only if method names match.
- Gates/Policies run only when you call them; `auth` middleware authenticates but does **not** authorize.
- A super-admin `before()` hook that returns `true` will also bypass `create`/`viewAny` — scope it carefully.

## Workflow
1. `php artisan make:policy PostPolicy --model=Post`; implement `update`/`delete` with the ownership rule.
2. In the controller, call `$this->authorize('update', $post)` before mutating (see [[laravel-form-requests-advanced]] for input rules).
3. For non-model rules, define a `Gate::define('manage-billing', ...)` in a service provider.
4. Cover both allow and deny paths in a Pest test; never rely on the UI hiding a button.

## Code Examples (Good vs Bad)

### Bad Example 1 (manual ownership check, misses admin)
```php
public function update(Request $request, Post $post) {
    if (auth()->id() !== $post->user_id) {   // duplicated everywhere, forgets admin override
        abort(403);
    }
    $post->update($request->all());          // also mass-assignment
}
```

### Bad Example 2 (authorization driven by client input)
```php
public function destroy(Request $request, Post $post) {
    if ($request->input('is_admin')) {       // client decides its own privilege
        $post->delete();
    }
}
```

### Bad Example 3 (authorize never called -> no check)
```php
public function update(UpdatePostRequest $request, Post $post): JsonResponse {
    $post->update($request->validated()); // a PostPolicy exists but is never invoked -> IDOR
    return response()->json($post);
}
```

### Bad Example 4 (over-broad before() bypasses everything)
```php
public function before(User $user): ?bool {
    return $user->is_staff ? true : null; // any staff member now passes delete/forceDelete too -> too wide
}
```

### Bad Example 5 (returning null where a deny was meant)
```php
public function delete(User $user, Post $post): ?bool {
    if ($user->id !== $post->user_id) { return null; } // null = "no opinion", not deny -> may fall through to allow
    return true;
}
```

### Good Example 1 (Policy holds the rule)
```php
// app/Policies/PostPolicy.php
public function update(User $user, Post $post): bool {
    return $user->id === $post->user_id || $user->hasRole('admin');
}
public function delete(User $user, Post $post): bool {
    return $this->update($user, $post);
}
```

### Good Example 2 (controller delegates to the Policy)
```php
// app/Http/Controllers/PostController.php
public function update(UpdatePostRequest $request, Post $post): JsonResponse {
    $this->authorize('update', $post);       // throws 403 via PostPolicy
    $post->update($request->validated());
    return response()->json(new PostResource($post));
}
```

### Good Example 3 (authorizeResource wires the whole controller)
```php
public function __construct() {
    $this->authorizeResource(Post::class, 'post'); // maps index/show/update/destroy to PostPolicy automatically
}
```

### Good Example 4 (Gate for a non-model rule)
```php
// AppServiceProvider::boot()
Gate::define('manage-billing', fn (User $user) => $user->hasRole('owner'));
// controller: Gate::authorize('manage-billing'); // 403 unless the owner
```

### Good Example 5 (explicit deny with a message)
```php
public function delete(User $user, Post $post): Response {
    return $user->id === $post->user_id
        ? Response::allow()
        : Response::deny('You can only delete your own posts.'); // clear 403 reason, never null
}
```

## Related skills
- [[laravel-sanctum-api-tokens]] — authenticate the request before authorizing it.
- [[laravel-middleware-auth]] — `auth`/`can` middleware as the first gate.
- [[laravel-security-hardening]] — authorization is one pillar of the OWASP defense.
- [[laravel-form-requests-advanced]] — Form Requests can also `authorize()` at the validation layer.

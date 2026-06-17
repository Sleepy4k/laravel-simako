---
name: laravel-api-resources
description: Shape JSON output with Laravel API Resources - explicit field whitelists, whenLoaded for relations, ResourceCollection, and consistent envelopes - instead of returning raw Eloquent models.
related:
  - laravel-form-requests-advanced
  - laravel-eloquent-lazy-loading
  - laravel-project-structure
  - laravel-security-hardening
---

# Laravel API Resources
- Every JSON endpoint returns through a Resource that whitelists exactly which fields are exposed.
- Relations are included with `whenLoaded()` so absent relations don't trigger lazy queries or null noise.
- Collections use a Resource collection for consistent pagination metadata and envelope shape.

## Safety contract: non-negotiable
- Abort if a controller returns a raw model/`->all()` as JSON (leaks `password`, `remember_token`, internal columns).
- Abort if a Resource references a relation directly (`$this->author->name`) without `whenLoaded()` (N+1 inside serialization).
- Abort if sensitive fields are conditionally hidden only in the UI rather than excluded from the Resource.
- Abort if the response envelope/casing differs per endpoint (clients can't rely on the contract).

## Required tools
- Laravel >= 11, PHP >= 8.2.

## Gotchas
- Returning a model directly works but serializes the full `$attributes` array — a Resource is the only reliable whitelist.
- `whenLoaded('author')` emits the key only if the relation was eager-loaded; otherwise it's omitted, not lazy-fetched.
- `$this->resource` is the underlying model inside `toArray()`; use it for derived/computed fields.
- A `ResourceCollection` preserves pagination links; wrapping a paginator in `collection()` keeps `meta`/`links`.

## Workflow
1. `php artisan make:resource OrderResource`; list each exposed field explicitly in `toArray()`.
2. Add relations with `whenLoaded()`; eager-load them in the query (see [[laravel-eloquent-lazy-loading]]).
3. Return `new OrderResource($order)` / `OrderResource::collection($paginator)` from the controller.
4. Set status codes via `->response()->setStatusCode(201)` for creates.

## Code Examples (Good vs Bad)

### Bad Example 1 (raw model leaks columns)
```php
public function show(User $user) {
    return response()->json($user);   // exposes password hash, remember_token, all columns
}
```

### Bad Example 2 (relation without whenLoaded -> N+1)
```php
public function toArray($request) {
    return [
        'id' => $this->id,
        'author' => $this->author->name,  // lazy query per item in a collection
    ];
}
```

### Bad Example 3 (hiding a field only in the UI)
```php
public function toArray($request) {
    return $this->resource->toArray(); // sends ssn/internal_notes; the frontend just doesn't render them
}
```

### Bad Example 4 (envelope shape differs per endpoint)
```php
// one controller returns: ['data' => $a]
// another returns:        ['result' => $b, 'ok' => true]   -> clients can't rely on a single contract
return ['result' => $orders, 'ok' => true];
```

### Bad Example 5 (paginator collected as a plain array)
```php
return response()->json(OrderResource::collection(Order::paginate(20))->toArray($request));
// flattening to an array drops the meta/links pagination envelope clients depend on
```

### Good Example 1 (explicit whitelist)
```php
// app/Http/Resources/UserResource.php
public function toArray(Request $request): array {
    return [
        'id'    => $this->id,
        'name'  => $this->name,
        'email' => $this->email,          // password/remember_token never listed
    ];
}
```

### Good Example 2 (conditional relation + collection)
```php
public function toArray(Request $request): array {
    return [
        'id'     => $this->id,
        'total'  => $this->total,
        'author' => new UserResource($this->whenLoaded('author')), // only if eager-loaded
    ];
}
// controller
return OrderResource::collection(
    Order::with('author')->paginate(20)   // meta + links preserved
);
```

### Good Example 3 (field shown only to permitted users)
```php
public function toArray(Request $request): array {
    return [
        'id'    => $this->id,
        'email' => $this->email,
        'notes' => $this->when($request->user()?->isAdmin(), $this->internal_notes), // omitted otherwise
    ];
}
```

### Good Example 4 (consistent envelope via a base resource)
```php
// app/Http/Resources/ApiResource.php
public function with(Request $request): array {
    return ['meta' => ['version' => 'v1']]; // every resource extending this shares one envelope shape
}
```

### Good Example 5 (collection keeps pagination meta)
```php
return OrderResource::collection(Order::with('author')->paginate(20)); // returns data + meta + links intact
```

## Related skills
- [[laravel-form-requests-advanced]] — validating input before shaping output.
- [[laravel-eloquent-lazy-loading]] — eager-loading the relations a Resource exposes.
- [[laravel-project-structure]] — Resources as the response boundary of the HTTP layer.
- [[laravel-security-hardening]] — whitelisting output to avoid data exposure.

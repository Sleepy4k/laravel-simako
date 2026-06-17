---
name: laravel-eloquent-lazy-loading
description: Eliminate Eloquent N+1 queries - eager-load with with(), constrain and count relations, and use preventLazyLoading in dev - instead of querying relations inside loops.
related:
  - laravel-best-practices
  - laravel-eloquent-transactions
  - laravel-api-resources
  - laravel-database-migrations
---

# Laravel Eloquent Lazy Loading
- Relations needed in a loop are eager-loaded up front with `with()`, turning N+1 into 2 queries.
- Counts use `withCount()`; constrained subsets use `with(['rel' => fn ($q) => ...])`.
- In development, `Model::preventLazyLoading()` makes accidental lazy loads throw instead of silently degrading.

## Safety contract: non-negotiable
- Abort if a relation is accessed inside a `foreach`/`map` without eager-loading it first (N+1 — one query per row).
- Abort if `->count()` on a relation runs per row instead of `withCount()` (N additional COUNT queries).
- Abort if lazy loading is left enabled in tests/dev where it would mask N+1 before production.
- Abort if eager loading pulls entire large relations when only a count/subset is needed (memory blowup).

## Required tools
- Laravel >= 11, PHP >= 8.2, a database with query logging (`DB::enableQueryLog`/Telescope).

## Gotchas
- `with('author')` eager-loads on the initial query; `load('author')` lazy-eager-loads after — both fix N+1, but `with()` is the default.
- `whenLoaded()` in a Resource only emits if `with()` ran — pair them or relations silently vanish.
- `preventLazyLoading(!app()->isProduction())` belongs in `AppServiceProvider::boot()`; it throws `LazyLoadingViolationException` on the offending relation.
- Nested eager loading uses dot notation: `with('posts.comments')`.

## Workflow
1. Identify relations read inside loops/serializers; add them to `with()` on the query.
2. Replace per-row `->count()` with `withCount('rel')` and read `rel_count`.
3. Constrain heavy relations with a closure to select only needed columns/rows.
4. Enable `preventLazyLoading` in non-production so regressions fail loudly.

## Code Examples (Good vs Bad)

### Bad Example 1 (classic N+1)
```php
$posts = Post::all();                 // 1 query
foreach ($posts as $post) {
    echo $post->author->name;         // +1 query EACH iteration -> N+1
}
```

### Bad Example 2 (per-row count)
```php
foreach (User::all() as $user) {
    echo $user->posts()->count();     // a COUNT query per user
}
```

### Bad Example 3 (nested N+1 in a serializer)
```php
$posts = Post::with('author')->get();      // comments NOT loaded
foreach ($posts as $post) {
    foreach ($post->comments as $c) {       // +1 query per post for comments -> N+1 again
        echo $c->body;
    }
}
```

### Bad Example 4 (eager-loading a huge relation for a count)
```php
$users = User::with('posts')->get();        // pulls EVERY post row into memory
foreach ($users as $user) { echo count($user->posts); } // only a number was needed
```

### Bad Example 5 (lazy loading allowed in tests)
```php
// no preventLazyLoading anywhere
$order = Order::first();
$order->items->sum('price'); // N+1 ships because nothing made it fail in dev/test
```

### Good Example 1 (eager load + constrained subset)
```php
$posts = Post::with(['author', 'comments' => fn ($q) => $q->latest()->limit(3)])
    ->get();                          // 3 queries total, regardless of row count
foreach ($posts as $post) {
    echo $post->author->name;
}
```

### Good Example 2 (withCount + prevent lazy loading in dev)
```php
$users = User::withCount('posts')->get();   // single query, posts_count populated
foreach ($users as $user) {
    echo $user->posts_count;
}
// app/Providers/AppServiceProvider.php@boot
Model::preventLazyLoading(! app()->isProduction()); // throws on any stray lazy load
```

### Good Example 3 (nested eager load via dot notation)
```php
$posts = Post::with('author', 'comments.user')->get(); // posts, authors, comments, comment-users: 4 queries
foreach ($posts as $post) {
    foreach ($post->comments as $c) { echo $c->user->name; }
}
```

### Good Example 4 (select only the columns needed)
```php
$posts = Post::with(['author:id,name'])->get(); // author rows carry just id+name, not every column
foreach ($posts as $post) { echo $post->author->name; }
```

### Good Example 5 (conditional lazy-eager load)
```php
$post = Post::find($id);
if ($includeComments) {
    $post->load('comments'); // one extra query, only when actually needed
}
```

## Related skills
- [[laravel-best-practices]] — N+1 avoidance as a standing rule.
- [[laravel-eloquent-transactions]] — wrapping multi-model writes safely.
- [[laravel-api-resources]] — `whenLoaded()` pairs with eager loading.
- [[laravel-database-migrations]] — indexes that keep eager loads fast.

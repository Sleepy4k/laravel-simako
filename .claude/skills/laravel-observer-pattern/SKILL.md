---
name: laravel-observer-pattern
description: Hook Eloquent model lifecycle events with Observers - created/updated/deleting/saving - for derived fields and cascade cleanup, while knowing when bulk operations skip them.
related:
  - laravel-events-listeners
  - laravel-eloquent-transactions
  - laravel-eloquent-lazy-loading
  - laravel-best-practices
---

# Laravel Observer Pattern
- Per-model lifecycle reactions (set a slug, clear a cache, cascade cleanup) live in an Observer, not scattered across controllers.
- Observers fire on `creating`/`created`/`updating`/`saving`/`deleting`/`deleted` for a single model's events.
- Code is aware that mass operations (`update`/`delete` on a query, `insert`) bypass model events.

## Safety contract: non-negotiable
- Abort if a uniqueness/derived-value invariant relies on an Observer but the code path uses `Model::query()->update(...)` (events don't fire — invariant skipped).
- Abort if an Observer does slow/external I/O synchronously inside `saving`/`creating` (it runs inside the request/transaction).
- Abort if an Observer mutates the model in `created` and forgets to `save()` (change is lost; mutate in `creating`/`saving` instead).
- Abort if cascade deletes are done only in a `deleting` observer while some deletes use `query()->delete()` (orphans left behind).

## Required tools
- Laravel >= 11, PHP >= 8.2.

## Gotchas
- `Builder::update()`/`delete()`/`insert()` and `upsert()` do NOT fire model events — use model instances or DB FKs for guaranteed cascades.
- Mutate attributes in `creating`/`saving` (before persistence); in `created` you'd need a second `save()`.
- Observers run inside the surrounding transaction — keep them fast; defer side effects to queued events.
- Register the observer (attribute `#[ObservedBy(...)]` on the model or in a provider) or it never fires.

## Workflow
1. `php artisan make:observer PostObserver --model=Post`.
2. Put derived-field logic in `creating`/`saving`; cleanup in `deleting`/`deleted`.
3. Register via `#[ObservedBy(PostObserver::class)]` on the model.
4. For invariants that must always hold, back them with DB constraints/FKs — not only the Observer.

## Code Examples (Good vs Bad)

### Bad Example 1 (mutate in created without saving)
```php
public function created(Post $post): void {
    $post->slug = Str::slug($post->title); // set after insert, never persisted -> slug stays null
}
```

### Bad Example 2 (invariant via observer, bypassed by bulk update)
```php
// PostObserver::saving sets published_at when status === 'published'
Post::where('batch', 5)->update(['status' => 'published']); // bypasses observer -> published_at null
```

### Bad Example 3 (slow external call inside saving)
```php
public function saving(Post $post): void {
    $post->summary = OpenAi::summarize($post->body); // synchronous HTTP inside the write transaction -> slow, fragile
}
```

### Bad Example 4 (observer never registered)
```php
class PostObserver {
    public function creating(Post $post): void { $post->slug = Str::slug($post->title); }
}
// no #[ObservedBy] on Post and no Post::observe(...) in a provider -> the hook never runs
```

### Bad Example 5 (cascade only in deleting, bulk delete orphans)
```php
public function deleting(Post $post): void { $post->comments()->delete(); }
// but elsewhere: Post::where('spam', true)->delete(); // bulk delete skips the observer -> orphaned comments
```

### Good Example 1 (derive in creating)
```php
class PostObserver {
    public function creating(Post $post): void {
        $post->slug ??= Str::slug($post->title);   // set before insert, persisted
    }
    public function deleting(Post $post): void {
        $post->comments()->delete();                // cascade via model events
    }
}
```

### Good Example 2 (registered observer + DB-level guarantee)
```php
#[ObservedBy(PostObserver::class)]
class Post extends Model { /* ... */ }
// migration backs the cascade so bulk deletes are also safe:
$table->foreignId('post_id')->constrained()->cascadeOnDelete();
```

### Good Example 3 (defer the slow work to a queued job)
```php
public function created(Post $post): void {
    SummarizePost::dispatch($post->id)->afterCommit(); // observer stays fast; AI call runs off-request
}
```

### Good Example 4 (register the observer in a provider)
```php
// AppServiceProvider::boot()
Post::observe(PostObserver::class); // explicit registration -> the hooks actually fire
```

### Good Example 5 (only recompute when the source changed)
```php
public function saving(Post $post): void {
    if ($post->isDirty('title')) { $post->slug = Str::slug($post->title); } // avoid needless work on every save
}
```

## Related skills
- [[laravel-events-listeners]] — explicit domain events vs model-lifecycle hooks.
- [[laravel-eloquent-transactions]] — observers run inside the transaction.
- [[laravel-eloquent-lazy-loading]] — relations touched during cascades.
- [[laravel-best-practices]] — keep observers thin and side-effects deferred.

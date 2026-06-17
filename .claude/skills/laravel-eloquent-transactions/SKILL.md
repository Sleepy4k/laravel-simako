---
name: laravel-eloquent-transactions
description: Make multi-step Laravel writes atomic - DB::transaction with automatic rollback, deadlock retries, lockForUpdate, and afterCommit for side effects - instead of partial, inconsistent saves.
related:
  - laravel-eloquent-lazy-loading
  - laravel-queue-optimization
  - laravel-events-listeners
  - laravel-best-practices
---

# Laravel Eloquent Transactions
- A unit of work that writes multiple rows runs inside `DB::transaction()`, which commits on success and rolls back on any exception.
- Row contention uses `lockForUpdate()` so concurrent requests don't double-spend/oversell.
- Side effects (mail, queue jobs, events) fire `afterCommit`, never before the data is durable.

## Safety contract: non-negotiable
- Abort if a sequence of dependent writes runs without a transaction (a mid-way failure leaves inconsistent data).
- Abort if a queue job/notification is dispatched inside the transaction without `afterCommit` (it may run before/without the commit).
- Abort if a read-modify-write on a balance/stock has no `lockForUpdate()` or atomic update (lost-update race).
- Abort if exceptions are swallowed inside the closure (the transaction commits a half-done state).

## Required tools
- Laravel >= 11, PHP >= 8.2, a transactional database (InnoDB/Postgres — not MyISAM).

## Gotchas
- `DB::transaction(fn)` auto-rolls-back on a thrown exception and re-throws; manual `beginTransaction`/`commit` needs try/catch.
- The second argument to `DB::transaction($fn, 3)` is the deadlock retry count — useful under contention.
- Jobs dispatched in a transaction can execute before the commit on a fast worker; use `dispatch(...)->afterCommit()` or set `after_commit` in the queue config.
- `lockForUpdate()` only holds within a transaction; outside one it's a no-op.

## Workflow
1. Wrap the dependent writes in `DB::transaction(function () { ... })`.
2. Lock contended rows with `lockForUpdate()` before reading the value you'll mutate.
3. Throw on any invariant violation so the whole unit rolls back.
4. Defer events/jobs/mail to `afterCommit` (see [[laravel-events-listeners]], [[laravel-queue-optimization]]).

## Code Examples (Good vs Bad)

### Bad Example 1 (no transaction, partial write)
```php
$order = Order::create($data);
$wallet->decrement('balance', $order->total);  // if this throws, the order already exists
OrderItem::insert($items);                      // never reached -> orphaned order
```

### Bad Example 2 (job dispatched before commit + lost update)
```php
DB::transaction(function () use ($user, $amount) {
    $user->balance = $user->balance - $amount;  // read-modify-write, no lock -> race
    $user->save();
    SendReceipt::dispatch($user);               // may run before commit -> emails a rollback
});
```

### Bad Example 3 (manual begin/commit, no catch)
```php
DB::beginTransaction();
$order = Order::create($data);
$wallet->decrement('balance', $order->total); // if this throws, commit never runs AND no rollback
DB::commit();                                  // the half-write stays locked/uncommitted
```

### Bad Example 4 (lockForUpdate outside a transaction)
```php
$user = User::lockForUpdate()->find($id); // no surrounding transaction -> the lock is a no-op
$user->decrement('balance', $amount);     // still races with concurrent debits
```

### Bad Example 5 (exception swallowed inside the closure)
```php
DB::transaction(function () use ($order, $items) {
    $order->save();
    try { $order->items()->createMany($items); } catch (\Throwable $e) { /* ignored */ }
    // the catch hides the failure -> the transaction commits an order with no items
});
```

### Good Example 1 (atomic unit, rollback on failure)
```php
DB::transaction(function () use ($data, $items, $wallet) {
    $order = Order::create($data);
    $order->items()->createMany($items);
    $wallet->decrement('balance', $order->total); // any throw rolls all of this back
    return $order;
});
```

### Good Example 2 (lock + afterCommit side effect + retries)
```php
DB::transaction(function () use ($userId, $amount) {
    $user = User::lockForUpdate()->findOrFail($userId); // serialize concurrent debits
    if ($user->balance < $amount) {
        throw new InsufficientFundsException();         // rolls back cleanly
    }
    $user->decrement('balance', $amount);
    SendReceipt::dispatch($user)->afterCommit();        // only after durable commit
}, attempts: 3);                                        // retry on deadlock
```

### Good Example 3 (atomic increment avoids the read-modify-write race)
```php
DB::transaction(function () use ($userId, $amount) {
    $affected = User::where('id', $userId)->where('balance', '>=', $amount)
        ->decrement('balance', $amount);          // single atomic UPDATE with a guard
    if ($affected === 0) { throw new InsufficientFundsException(); } // nothing matched -> roll back
});
```

### Good Example 4 (manual transaction done correctly)
```php
DB::beginTransaction();
try {
    $order = Order::create($data);
    $order->items()->createMany($items);
    DB::commit();
} catch (\Throwable $e) {
    DB::rollBack();   // explicit rollback, then re-raise
    throw $e;
}
```

### Good Example 5 (event deferred to afterCommit)
```php
DB::transaction(function () use ($order) {
    $order->markPaid();
    event(new OrderPaid($order)); // listeners marked afterCommit fire only once the data is durable
});
```

## Related skills
- [[laravel-eloquent-lazy-loading]] — efficient reads alongside transactional writes.
- [[laravel-queue-optimization]] — dispatching jobs safely after commit.
- [[laravel-events-listeners]] — events that should also fire afterCommit.
- [[laravel-best-practices]] — atomicity as a correctness rule.

---
name: laravel-events-listeners
description: Decouple side effects with Laravel events and listeners - auto-discovered listeners, ShouldQueue for slow work, and afterCommit dispatch - instead of stuffing every reaction into the controller.
related:
  - laravel-observer-pattern
  - laravel-notification-system
  - laravel-queue-optimization
  - laravel-eloquent-transactions
---

# Laravel Events Listeners
- A domain happening is an Event; each reaction is a separate Listener, so adding a reaction doesn't touch the controller.
- Slow listeners implement `ShouldQueue` and run off-request.
- Events tied to a DB write dispatch `afterCommit`, so listeners never act on rolled-back data.

## Safety contract: non-negotiable
- Abort if a listener does slow I/O (mail, HTTP) synchronously without `ShouldQueue` (blocks the request).
- Abort if an event is dispatched mid-transaction and a listener reads data not yet committed (use `afterCommit`).
- Abort if a listener silently swallows exceptions so a failed reaction goes unnoticed (no retry/logging).
- Abort if business-critical invariants live only in a listener that may be queued/delayed (eventual ≠ guaranteed for must-happen-now rules).

## Required tools
- Laravel >= 11 (event auto-discovery), PHP >= 8.2, a queue driver for queued listeners.

## Gotchas
- Laravel 11 auto-discovers listeners by their `handle()`/`__invoke()` type-hint — no manual `$listen` map needed.
- A queued listener serializes the event; reference IDs, not heavy objects, and dispatch the event `afterCommit`.
- `Event::fake()` in tests asserts dispatch without running listeners — great for unit isolation.
- Multiple listeners on one event run in registration/discovery order; don't assume one finished before another starts when queued.

## Workflow
1. Define an event (`make:event OrderPlaced`) carrying the minimal payload (the model/id).
2. Create one listener per reaction (`make:listener SendReceipt --event=OrderPlaced`).
3. Mark slow listeners `ShouldQueue`; dispatch the event `afterCommit` when it follows a write.
4. Test with `Event::fake()`/`Event::assertDispatched`.

## Code Examples (Good vs Bad)

### Bad Example 1 (every reaction inline in the controller)
```php
public function store(StoreOrderRequest $r) {
    $order = Order::create($r->validated());
    Mail::to($order->user)->send(new Receipt($order)); // blocks
    Http::post('https://erp/sync', $order->toArray());  // blocks; failure breaks checkout
    Slack::send("new order {$order->id}");              // controller knows too much
}
```

### Bad Example 2 (event mid-transaction, sync slow listener)
```php
DB::transaction(function () use ($data) {
    $order = Order::create($data);
    OrderPlaced::dispatch($order);   // listeners run before commit; SendReceipt does sync SMTP
});
```

### Bad Example 3 (listener swallows its own failure)
```php
class SyncToErp {
    public function handle(OrderPlaced $event): void {
        try { Http::post('https://erp/sync', $event->order->toArray()); }
        catch (\Throwable $e) { /* ignored */ } // a failed sync vanishes -> no retry, no alert
    }
}
```

### Bad Example 4 (must-happen-now rule in a queued listener)
```php
class ReserveStock implements ShouldQueue {
    public function handle(OrderPlaced $event): void {
        $event->order->product->decrement('stock'); // queued/delayed -> overselling between order and reservation
    }
}
```

### Bad Example 5 (heavy object serialized into the queue)
```php
class OrderPlaced {
    public function __construct(public Order $orderWithAllRelations) {} // serializes the full graph -> bloated payload, stale on run
}
```

### Good Example 1 (thin controller dispatches an event)
```php
public function store(StoreOrderRequest $r): JsonResponse {
    $order = $this->orders->place($r->validated());
    OrderPlaced::dispatch($order);                       // reactions live elsewhere
    return (new OrderResource($order))->response()->setStatusCode(201);
}
```

### Good Example 2 (queued listener + afterCommit event)
```php
class SendReceipt implements ShouldQueue {
    public function handle(OrderPlaced $event): void {
        $event->order->user->notify(new InvoicePaid($event->order)); // off-request
    }
}
// event dispatched only after the write is durable
class OrderPlaced { public function __construct(public Order $order) {} }
// dispatch(...)->afterCommit() or set after_commit in config/queue.php
```

### Good Example 3 (queued listener with a failed() hook)
```php
class SyncToErp implements ShouldQueue {
    public int $tries = 3;
    public function handle(OrderPlaced $event): void { Http::post('https://erp/sync', ['id' => $event->orderId]); }
    public function failed(OrderPlaced $event, \Throwable $e): void { report($e); } // retried, then alerted
}
```

### Good Example 4 (reserve stock synchronously, notify async)
```php
DB::transaction(function () use ($data) {
    $order = $this->orders->place($data); // stock reserved inside the transaction -> no oversell
    OrderPlaced::dispatch($order->id);    // only the slow, non-critical reactions are queued
});
```

### Good Example 5 (carry an id, reload in the listener)
```php
class OrderPlaced {
    public function __construct(public int $orderId) {} // tiny payload
}
class SendReceipt implements ShouldQueue {
    public function handle(OrderPlaced $e): void { Order::find($e->orderId)?->user->notify(new Receipt()); } // fresh data
}
```

## Related skills
- [[laravel-observer-pattern]] — model-lifecycle hooks vs explicit domain events.
- [[laravel-notification-system]] — a common listener reaction.
- [[laravel-queue-optimization]] — reliability rules for queued listeners.
- [[laravel-eloquent-transactions]] — afterCommit dispatch semantics.

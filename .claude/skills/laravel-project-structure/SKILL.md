---
name: laravel-project-structure
description: Lay out an enterprise Laravel app with thin controllers, Form Requests, a Service layer, Eloquent models, and API Resources - so HTTP, validation, business logic, and persistence stay separated.
related:
  - laravel-initiation-scaffold
  - laravel-best-practices
  - laravel-form-requests-advanced
  - laravel-service-provider-binding
---

# Laravel Enterprise Project Structure
- Controllers stay thin: they receive a validated Form Request, call a Service, and return an API Resource.
- Business logic lives in `app/Services`; persistence detail stays in Eloquent models and (optionally) repositories.
- HTTP shape (validation, serialization, status codes) never leaks into the domain layer.

## Safety contract: non-negotiable
- Abort if a controller action contains business logic beyond orchestration (calculations, multi-step writes belong in a Service).
- Abort if `$request->all()` is mass-assigned — validate through a Form Request and pass `->validated()`.
- Abort if a Service returns a `redirect()`/`response()` — HTTP responses belong to the controller adapter only.
- Abort if Eloquent models are queried directly inside Blade templates (N+1 and untestable views).

## Required tools
- Laravel >= 11, PHP >= 8.2, Composer.

## Gotchas
- "Fat controller" is the default trap: `php artisan make:controller` gives you a class with no guard rails — discipline is yours.
- A Service that type-hints `Request` is coupled to HTTP; pass the validated array or a DTO instead, so it is reusable from Artisan/queues.
- Returning raw models from JSON endpoints leaks every column (including `password`/`remember_token`); wrap in an API Resource.
- Putting business rules in model accessors/mutators spreads logic across the schema layer — keep models thin.

## Workflow
1. Generate a Form Request (`make:request`) and put rules + authorization there (see [[laravel-form-requests-advanced]]).
2. Generate a thin controller; inject the Service and the Form Request via the method signature.
3. Implement the use case in `app/Services/<Domain>Service.php`, returning models/DTOs — never HTTP responses.
4. Wrap the result in an API Resource for the JSON shape; bind interfaces in a provider (see [[laravel-service-provider-binding]]).

## Code Examples (Good vs Bad)

### Bad Example 1 (fat controller, mass assignment, raw model out)
```php
class OrderController extends Controller {
    public function store(Request $request) {
        $order = Order::create($request->all());   // mass-assignment + no validation
        $order->total = $order->items->sum('price') * 1.1;  // business logic in controller
        $order->save();
        return response()->json($order);           // leaks every column
    }
}
```

### Bad Example 2 (Service coupled to HTTP)
```php
class OrderService {
    public function checkout(Request $request) {     // depends on HTTP; unusable from a queue job
        $order = Order::create($request->validated());
        return redirect()->route('orders.show', $order); // redirect from the domain layer
    }
}
```

### Bad Example 3 (business rule hidden in a model accessor)
```php
class Order extends Model {
    public function getTotalAttribute(): float {
        return $this->items->sum('price') * (1 + TaxApi::rateFor($this->country)); // external call + N+1 in an accessor
    }
}
```

### Bad Example 4 (query inside a Blade view)
```php
{{-- resources/views/dashboard.blade.php --}}
@foreach (\App\Models\Order::with('user')->latest()->get() as $order)
    {{ $order->user->name }}  {{-- query logic in the view -> untestable, hard to cache --}}
@endforeach
```

### Bad Example 5 (repository returns an HTTP response)
```php
class OrderRepository {
    public function find(int $id) {
        $order = Order::find($id);
        return $order ?: response()->json(['error' => 'not found'], 404); // persistence layer leaking HTTP
    }
}
```

### Good Example 1 (thin controller delegating to a Service)
```php
// app/Http/Controllers/OrderController.php
class OrderController extends Controller {
    public function __construct(private readonly OrderService $orders) {}

    public function store(StoreOrderRequest $request): JsonResponse {
        $order = $this->orders->checkout($request->validated());
        return (new OrderResource($order))->response()->setStatusCode(201);
    }
}
```

### Good Example 2 (Service holds the business rule, returns a model)
```php
// app/Services/OrderService.php
class OrderService {
    public function checkout(array $data): Order {
        return DB::transaction(function () use ($data) {
            $order = Order::create($data);
            $order->update(['total' => $order->items()->sum('price') * 1.1]);
            return $order->fresh('items');
        });
    }
}
```

### Good Example 3 (DTO carries data into the Service)
```php
// app/DTO/CheckoutData.php
final readonly class CheckoutData {
    public function __construct(public array $items, public int $userId) {}
    public static function fromRequest(StoreOrderRequest $r): self {
        return new self($r->validated()['items'], $r->user()->id); // HTTP -> plain data at the boundary
    }
}
```

### Good Example 4 (view receives prepared data, no queries)
```php
// controller passes a ready collection; the view only renders
public function index(): View {
    return view('dashboard', ['orders' => $this->orders->recentForDashboard()]); // logic stays in the service
}
```

### Good Example 5 (repository interface bound in a provider)
```php
// app/Providers/AppServiceProvider.php
$this->app->bind(OrderRepository::class, EloquentOrderRepository::class); // swap persistence without touching services
```

## Related skills
- [[laravel-initiation-scaffold]] — bootstrap a project with this layout from zero.
- [[laravel-best-practices]] — the quality gate that keeps the layers separated.
- [[laravel-form-requests-advanced]] — where validation + authorization live.
- [[laravel-service-provider-binding]] — binding service/repository interfaces to implementations.

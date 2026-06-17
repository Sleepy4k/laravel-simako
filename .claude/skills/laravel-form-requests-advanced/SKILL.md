---
name: laravel-form-requests-advanced
description: Validate and authorize input in Laravel Form Requests - rules(), authorize(), prepareForValidation, after hooks, and Rule objects - so controllers receive only clean, permitted data.
related:
  - laravel-basic-code
  - laravel-policy-gate-auth
  - laravel-api-resources
  - laravel-security-hardening
---

# Laravel Form Requests Advanced
- Each write endpoint has a Form Request whose `rules()` defines validation and `authorize()` gates access.
- Input is normalized in `prepareForValidation()` and cross-field checks run in `after()`/`withValidator()`.
- Controllers consume `$request->validated()` only — never `$request->all()`.

## Safety contract: non-negotiable
- Abort if `authorize()` returns `true` unconditionally on an endpoint that needs per-user authorization.
- Abort if the controller uses `$request->all()`/`input()` instead of `validated()` (unvalidated fields slip through).
- Abort if a uniqueness rule on update doesn't ignore the current record (`Rule::unique(...)->ignore($id)`).
- Abort if validation rules trust a client-supplied field that controls privilege (e.g. `role`, `is_admin`).

## Required tools
- Laravel >= 11, PHP >= 8.2.

## Gotchas
- A Form Request's `authorize()` returning `false` yields a 403 before validation runs — don't put auth logic in `rules()`.
- `validated()` returns only keys that had rules; a field with no rule is silently dropped (often what you want).
- `prepareForValidation()` runs before rules — use it to trim/cast, not to bypass validation.
- `sometimes()`/`Rule::when()` apply conditional rules; without them, optional fields still hit `required` errors.

## Workflow
1. `php artisan make:request StoreOrderRequest`; implement `rules()` and `authorize()`.
2. Normalize input in `prepareForValidation()` (trim, lowercase email, cast types).
3. Add cross-field/business checks in `after()`; ignore-self on update uniqueness.
4. In the controller, type-hint the request and pass `validated()` to the Service (see [[laravel-basic-code]]).

## Code Examples (Good vs Bad)

### Bad Example 1 (open authorize, all() in controller)
```php
class StoreOrderRequest extends FormRequest {
    public function authorize(): bool { return true; }   // anyone may submit
    public function rules(): array { return ['qty' => 'required']; }
}
public function store(StoreOrderRequest $r) {
    Order::create($r->all());                            // bypasses validation entirely
}
```

### Bad Example 2 (unique without ignore on update)
```php
public function rules(): array {
    return ['email' => 'required|email|unique:users'];   // fails when user keeps their own email
}
```

### Bad Example 3 (trusting a client-supplied privilege field)
```php
public function rules(): array {
    return ['name' => 'required', 'role' => 'required']; // client sends role=admin -> privilege escalation
}
```

### Bad Example 4 (auth logic stuffed into rules())
```php
public function rules(): array {
    if (! $this->user()->owns($this->route('order'))) { abort(403); } // auth belongs in authorize()
    return ['note' => 'string'];
}
```

### Bad Example 5 (prepareForValidation injects an unvalidated field)
```php
protected function prepareForValidation(): void {
    $this->merge(['status' => $this->input('status', 'approved')]); // 'status' has no rule -> bypasses validation
}
```

### Good Example 1 (authorize + normalize + validated)
```php
class StoreOrderRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('create', Order::class);
    }
    protected function prepareForValidation(): void {
        $this->merge(['email' => strtolower(trim($this->email))]);
    }
    public function rules(): array {
        return ['email' => 'required|email', 'qty' => 'required|integer|min:1'];
    }
}
```

### Good Example 2 (update ignores self, conditional + cross-field)
```php
public function rules(): array {
    return [
        'email' => ['required', 'email', Rule::unique('users')->ignore($this->user()->id)],
        'discount' => ['nullable', 'integer', Rule::when($this->filled('coupon'), ['max:50'])],
    ];
}
public function after(): array {
    return [function ($v) {
        if ($this->starts_at >= $this->ends_at) $v->errors()->add('ends_at', 'must be after start');
    }];
}
```

### Good Example 3 (role validated against an allowlist)
```php
public function rules(): array {
    return ['role' => ['required', Rule::in(['member', 'editor'])]]; // 'admin' can never be assigned via input
}
```

### Good Example 4 (conditional rule with sometimes)
```php
public function rules(): array {
    return ['shipping_address' => 'sometimes|required|string']; // required only when the field is present
}
```

### Good Example 5 (custom Rule object encapsulates the check)
```php
public function rules(): array {
    return ['coupon' => ['nullable', new ValidCoupon($this->user())]]; // reusable, testable rule object
}
```

## Related skills
- [[laravel-basic-code]] — consuming `validated()` in the controller.
- [[laravel-policy-gate-auth]] — deeper authorization beyond `authorize()`.
- [[laravel-api-resources]] — shaping the response after input is validated.
- [[laravel-security-hardening]] — validation as the first line against injection/mass-assignment.

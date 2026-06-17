---
name: laravel-logging-monolog-channels
description: Configure Laravel logging with Monolog channels - stack/daily/slack, structured context, log levels, PII redaction, and request correlation - instead of unstructured logs that leak secrets.
related:
  - laravel-security-hardening
  - laravel-best-practices
  - laravel-horizon-redis-monitor
  - laravel-runtime-compatibility
---

# Laravel Logging Monolog Channels
- Logs route through named channels (`stack`, `daily`, `slack`) configured in `config/logging.php`, chosen by severity.
- Entries carry structured context (`['order_id' => ...]`), not interpolated strings, so they're searchable.
- Secrets and PII are redacted before logging; correlation IDs tie a request's lines together.

## Safety contract: non-negotiable
- Abort if passwords, tokens, full card/PII, or `.env` values are written to logs (sensitive-data exposure).
- Abort if errors are logged at `info`/`debug` (or success at `error`) so severity-based alerting/filtering breaks.
- Abort if a whole request/response body is logged unfiltered (leaks credentials and bloats storage).
- Abort if logs go only to a single file with no rotation in production (disk fills, no retention policy).

## Required tools
- Laravel >= 11, Monolog 3, PHP >= 8.2; a Slack webhook or log aggregator for the alert channel.

## Gotchas
- `Log::info("user {$user->email} did X")` bakes PII into the message — pass context as an array and redact it instead.
- The `single` driver never rotates; use `daily` (with `days`) or ship to an aggregator in production.
- A Monolog processor can redact/inject globally (e.g. strip `password`/`token` keys, add a request id) — wire it once.
- Channel levels filter: a `slack` channel at `critical` won't receive `error` — set the threshold deliberately.

## Workflow
1. Define channels in `config/logging.php`: a `daily` file + a `slack` channel at `critical`.
2. Add a Monolog processor that redacts secret keys and adds a request/correlation id.
3. Log with structured context and the right level; never interpolate PII into the message.
4. Route severities to channels (errors → slack/aggregator) and set retention (`days`).

## Code Examples (Good vs Bad)

### Bad Example 1 (PII + secrets in the message, wrong level)
```php
Log::info("login {$user->email} pw={$request->password} token={$apiToken}"); // leaks creds + PII
Log::debug('Payment gateway 500 error');   // an error logged at debug -> missed by alerts
```

### Bad Example 2 (dump whole request, single non-rotating file)
```php
Log::channel('single')->info('request', $request->all()); // logs passwords/tokens, file grows forever
```

### Bad Example 3 (logging inside a tight loop)
```php
foreach ($rows as $row) {
    Log::info('processing row', ['row' => $row->toArray()]); // millions of lines -> fills disk, drowns real signal
    process($row);
}
```

### Bad Example 4 (request() used in a queue/CLI context)
```php
// inside a queued job
Log::info('job ran', ['ip' => request()->ip()]); // request() is null on the worker -> error in the log path itself
```

### Bad Example 5 (single non-rotating file in production)
```php
// config/logging.php
'default' => 'single', // one ever-growing file, no retention -> disk fills, nothing rotates
```

### Good Example 1 (structured context + correct level)
```php
Log::warning('login.failed', [
    'user_id' => $user->id,                 // id, not email; no password
    'ip'      => $request->ip(),
]);
Log::channel('slack')->critical('payment.gateway_down', ['gateway' => 'stripe']);
```

### Good Example 2 (redacting processor + daily rotation)
```php
// config/logging.php
'daily' => ['driver' => 'daily', 'path' => storage_path('logs/app.log'),
            'level' => 'debug', 'days' => 14, 'tap' => [RedactSecrets::class]],

// app/Logging/RedactSecrets.php
public function __invoke(Logger $logger): void {
    $logger->pushProcessor(function (LogRecord $r) {
        foreach (['password', 'token', 'authorization'] as $k) {
            if (isset($r->context[$k])) $r->context[$k] = '[redacted]';
        }
        return $r->withExtra(['request_id' => request()?->header('X-Request-Id')]);
    });
}
```

### Good Example 3 (stack channel routes by severity)
```php
// config/logging.php
'stack' => ['driver' => 'stack', 'channels' => ['daily', 'slack'], 'ignore_exceptions' => false],
'slack' => ['driver' => 'slack', 'url' => env('LOG_SLACK_WEBHOOK_URL'), 'level' => 'critical'], // only critical pages on-call
```

### Good Example 4 (summarize a loop, log once)
```php
$failed = 0;
foreach ($rows as $row) { try { process($row); } catch (\Throwable $e) { $failed++; } }
Log::warning('batch.completed', ['total' => $rows->count(), 'failed' => $failed]); // one searchable summary line
```

### Good Example 5 (correlation id without request() in CLI)
```php
$requestId = app()->runningInConsole() ? (string) Str::uuid() : request()->header('X-Request-Id');
Log::withContext(['request_id' => $requestId]); // every subsequent line carries it, works in CLI and HTTP
```

## Related skills
- [[laravel-security-hardening]] — not logging secrets is a security control.
- [[laravel-best-practices]] — structured logging as a standing rule.
- [[laravel-horizon-redis-monitor]] — queue/worker failures surface through logs/alerts.
- [[laravel-runtime-compatibility]] — `request()` may be absent in CLI/queue log context.

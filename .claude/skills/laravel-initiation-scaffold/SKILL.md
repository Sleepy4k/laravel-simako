---
name: laravel-initiation-scaffold
description: Bootstrap a new Laravel API from zero - composer create-project, .env + key:generate, migrations, Sanctum, and the layered app/ tree - ready to serve authenticated JSON.
related:
  - laravel-project-structure
  - laravel-sanctum-api-tokens
  - laravel-database-migrations
  - laravel-best-practices
---

# Laravel Initiation Scaffold
- A new project starts from `composer create-project`, an `.env` with a generated `APP_KEY`, and a migrated database.
- The `app/` tree is organized into Http/Services/Models before the first feature lands.
- API authentication (Sanctum) and a health route are wired before shipping any business endpoint.

## Safety contract: non-negotiable
- Abort if `APP_KEY` is empty/committed — without it, encryption and signed cookies are broken/insecure.
- Abort if `.env` (with real secrets) is committed; only `.env.example` belongs in git.
- Abort if `APP_DEBUG=true` is set in a production `.env` (leaks stack traces and config).
- Abort if the app is served from the project root instead of `public/` (exposes `.env`, `vendor`, source).

## Required tools
- Composer, PHP >= 8.2, Laravel installer or `composer create-project laravel/laravel`, a database (MySQL/Postgres/SQLite).

## Gotchas
- `php artisan key:generate` writes into `.env`; run it once per environment, never reuse a key across deploys carelessly.
- `composer create-project` already gitignores `.env`; don't force-add it.
- The default `php artisan serve` is dev-only — production runs through a real web server pointed at `public/`.
- Run `php artisan migrate` against the right `.env` DB; a fresh SQLite needs the file created first.

## Workflow
1. `composer create-project laravel/laravel my-api` then `cd my-api`.
2. `cp .env.example .env && php artisan key:generate`; set DB credentials.
3. `php artisan migrate`; install Sanctum and publish its config (see [[laravel-sanctum-api-tokens]]).
4. Create the `app/Services` layer and a `/up` health route; commit `.env.example` only.

## Code Examples (Good vs Bad)

### Bad Example 1 (committed secrets, debug on)
```bash
git add .env                 # leaks DB password, APP_KEY, API secrets
# .env contains: APP_DEBUG=true on production -> full stack traces to clients
```

### Bad Example 2 (no app key)
```bash
composer create-project laravel/laravel my-api
# skipped key:generate -> APP_KEY= empty
# RuntimeException: No application encryption key has been specified.
```

### Bad Example 3 (served from the project root)
```nginx
server { root /var/www/my-api; index index.php; } # exposes /.env, /vendor, /storage to the web
```

### Bad Example 4 (one APP_KEY copied across environments)
```bash
# same APP_KEY pasted into staging and prod .env
APP_KEY=base64:SHAREDKEY== # a leak in one env decrypts cookies/data in the other
```

### Bad Example 5 (migrate against the wrong database)
```bash
php artisan migrate --force   # run with the local .env still pointing at the prod DB by mistake -> wrong schema applied
```

### Good Example 1 (correct bootstrap)
```bash
composer create-project laravel/laravel my-api && cd my-api
cp .env.example .env
php artisan key:generate          # writes APP_KEY into .env
php artisan migrate               # build the schema
```

### Good Example 2 (Sanctum + layered tree before features)
```bash
php artisan install:api           # installs Sanctum, creates routes/api.php
mkdir -p app/Services app/Repositories
# routes/web.php: Route::get('/up', fn () => response()->json(['status' => 'ok']));
echo ".env" >> .gitignore         # already ignored by default; verify
```

### Good Example 3 (web server points at public/)
```nginx
server { root /var/www/my-api/public; index index.php; } # only public/ is reachable
```

### Good Example 4 (per-environment key, never shared)
```bash
# generate a distinct key in each environment
php artisan key:generate --force   # staging and prod each get their own APP_KEY
```

### Good Example 5 (health route + example env committed)
```bash
# routes/web.php: Route::get('/up', fn () => response()->json(['status' => 'ok']));
git add .env.example   # the template is tracked; the real .env stays ignored
```

## Related skills
- [[laravel-project-structure]] — the layered layout to scaffold into.
- [[laravel-sanctum-api-tokens]] — API auth installed during bootstrap.
- [[laravel-database-migrations]] — building the schema you just migrated.
- [[laravel-best-practices]] — the gate to wire into CI from day one.

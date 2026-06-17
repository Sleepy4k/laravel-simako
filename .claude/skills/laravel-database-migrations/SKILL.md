---
name: laravel-database-migrations
description: Evolve a Laravel schema safely with migrations - reversible down(), foreign keys and indexes, no destructive edits to shipped migrations, and zero-downtime column changes.
related:
  - laravel-eloquent-lazy-loading
  - laravel-eloquent-transactions
  - laravel-best-practices
  - laravel-initiation-scaffold
---

# Laravel Database Migrations
- Schema changes are versioned migrations with a working `down()` so they can roll back.
- Foreign keys and indexes are declared in the migration, not added ad hoc in the DB.
- Already-shipped migrations are never edited; new changes get new migration files.

## Safety contract: non-negotiable
- Abort if a migration that has run in another environment is edited (history diverges; `migrate` won't re-run it).
- Abort if `down()` is empty/incorrect on a reversible change (rollback corrupts the schema).
- Abort if a destructive migration (`dropColumn`, `drop`) runs on production without a backup/confirmation.
- Abort if a foreign key column has no index and no `constrained()` (orphaned rows, slow joins).

## Required tools
- Laravel >= 11, PHP >= 8.2, a relational database, `doctrine/dbal` only if renaming columns on older drivers.

## Gotchas
- `migrate:fresh` drops ALL tables before re-running — never on production; it's a local/CI tool.
- `foreignId('user_id')->constrained()` creates both the column and the FK + index; bare `unsignedBigInteger` does not.
- Renaming/changing columns historically needed `doctrine/dbal`; on Laravel 11 native `change()` covers common cases — check your driver.
- Adding a NOT NULL column without a default to a populated table fails — add nullable or a default, backfill, then tighten.

## Workflow
1. `php artisan make:migration create_orders_table`; define `up()` columns, indexes, FKs.
2. Write a real `down()` that reverses `up()` exactly.
3. For column changes on big tables, split into add-nullable → backfill → enforce, to stay online.
4. Run `php artisan migrate` (and test `migrate:rollback`); never edit a migration others have run.

## Code Examples (Good vs Bad)

### Bad Example 1 (no down, no index on FK)
```php
public function up(): void {
    Schema::create('orders', function (Blueprint $t) {
        $t->id();
        $t->unsignedBigInteger('user_id');   // no FK, no index -> slow joins, orphans
        $t->decimal('total', 10, 2);
    });
}
public function down(): void {}              // rollback does nothing -> broken
```

### Bad Example 2 (destructive change to a shipped migration / fresh on prod)
```php
// editing 2024_01_01_create_users_table.php after it ran in staging:
$table->dropColumn('email');                 // history diverges; teammates' DBs differ
// then on production:
// php artisan migrate:fresh                  -> DROPS every table and all data
```

### Bad Example 3 (NOT NULL with no default on a populated table)
```php
public function up(): void {
    Schema::table('users', fn (Blueprint $t) => $t->string('country')); // not null, no default
    // fails immediately: existing rows have no value for the new required column
}
```

### Bad Example 4 (dropColumn loses data with no recovery)
```php
public function up(): void {
    Schema::table('users', fn (Blueprint $t) => $t->dropColumn('legacy_notes')); // data gone
}
public function down(): void {
    Schema::table('users', fn (Blueprint $t) => $t->text('legacy_notes')->nullable()); // recreates the column, NOT the data
}
```

### Bad Example 5 (rolling out an index on a huge table inline)
```php
public function up(): void {
    Schema::table('events', fn (Blueprint $t) => $t->index('created_at')); // locks the big table during the build -> downtime
}
```

### Good Example 1 (constrained FK, indexes, reversible)
```php
public function up(): void {
    Schema::create('orders', function (Blueprint $t) {
        $t->id();
        $t->foreignId('user_id')->constrained()->cascadeOnDelete(); // column + FK + index
        $t->decimal('total', 10, 2);
        $t->string('status')->index();
        $t->timestamps();
    });
}
public function down(): void {
    Schema::dropIfExists('orders');           // exact reverse
}
```

### Good Example 2 (zero-downtime three-step column add)
```php
// migration 1: add nullable
Schema::table('users', fn (Blueprint $t) => $t->string('country')->nullable());
// (deploy code, backfill: User::whereNull('country')->update(['country' => 'US']);)
// migration 2: enforce once populated
Schema::table('users', fn (Blueprint $t) => $t->string('country')->nullable(false)->change());
```

### Good Example 3 (composite unique constraint, reversible)
```php
public function up(): void {
    Schema::table('memberships', fn (Blueprint $t) => $t->unique(['team_id', 'user_id'])); // one membership per pair
}
public function down(): void {
    Schema::table('memberships', fn (Blueprint $t) => $t->dropUnique(['team_id', 'user_id']));
}
```

### Good Example 4 (chunked backfill, online-safe)
```php
public function up(): void {
    DB::table('users')->whereNull('country')->orderBy('id')->chunkById(1000, function ($rows) {
        DB::table('users')->whereIn('id', $rows->pluck('id'))->update(['country' => 'US']); // batched, no full-table lock
    });
}
```

### Good Example 5 (drop a column safely behind a backup step)
```php
public function up(): void {
    // assumes the legacy_notes data was exported in a prior deploy step
    Schema::table('users', fn (Blueprint $t) => $t->dropColumn('legacy_notes'));
}
public function down(): void {
    Schema::table('users', fn (Blueprint $t) => $t->text('legacy_notes')->nullable()); // shape restored for rollback
}
```

## Related skills
- [[laravel-eloquent-lazy-loading]] — indexes here keep eager loads fast.
- [[laravel-eloquent-transactions]] — DDL plus transactional data backfills.
- [[laravel-best-practices]] — migration hygiene as a team rule.
- [[laravel-initiation-scaffold]] — the first migrations in a new project.

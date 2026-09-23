# Quickstart: Idempotency Key Entity

**Date**: 2026-09-23
**Feature**: [spec.md](./spec.md) | [data-model.md](./data-model.md)

## Purpose

Prove the idempotency key feature works end-to-end: a key that has never been seen is logged and returns `true`; a repeat of an already-logged key returns `false` and does not create a duplicate.

## Prerequisites

- Laravel app up and database migrated (`php artisan migrate`)
- The `idempotency_keys` table exists (migration `2026_09_23_203551_create_idempotency_keys_table`)

## Run Scenarios

Run inside the project (e.g., `php artisan tinker` or a feature test):

### Scenario 1: New key returns true and is persisted

```php
LogIdempotencyKey::execute('unique-key-abc'); // true
log(IdempotencyKey::where('key', 'unique-key-abc')->exists()); // true
```

**Expected**: first call returns `true`; the record exists afterwards (SC-001, SC-002).

### Scenario 2: Duplicate key returns false and adds nothing

```php
LogIdempotencyKey::execute('unique-key-abc'); // false
log(IdempotencyKey::where('key', 'unique-key-abc')->count()); // 1
```

**Expected**: second call returns `false`; only one row exists (SC-004, FR-005).

### Scenario 3: Empty key rejected

```php
LogIdempotencyKey::execute(''); // rejects (empty) or database rejects
```

**Expected**: an empty key is not persisted (FR-002, SC-003).

## Automated Checks

Add a PHPUnit feature test asserting:

1. `LogIdempotencyKey::execute($key)` is `true` then `false` for the same key.
2. Record count stays at 1 after both calls.
3. Empty string returns cannot produce a stored empty `key` row.
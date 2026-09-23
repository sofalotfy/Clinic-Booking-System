# Research: Idempotency Key Entity

**Date**: 2026-09-23
**Feature**: [spec.md](./spec.md)

## Unknowns from Technical Context

No `NEEDS CLARIFICATION` markers remain in the spec. Two design decisions were evaluated:

### Decision 1: How to guarantee uniqueness while checking-then-inserting

**Decision**: Rely on a database-level unique index on the `key` column, combined with an existence check in the service. The existence check (`where('key', $key)->exists()`) provides the fast `true`/`false` return contract; the unique index is the final guard so no duplicate row can be inserted even under concurrent calls.

**Rationale**: The service contract requires "insert if absent, return `false` if present". An existence check alone is vulnerable to a race between the check and the insert under concurrent requests. The unique index turns any race into a failed insert rather than duplicated data, preserving SC-004 (no duplicates) regardless of concurrency.

**Alternatives considered**:
- Unique-index only (rely on insert exceptions): still correct but the service would need try/catch around every insert, making the `true`/`false` contract less readable.
- `firstOrCreate`: closer in spirit but creates a record from any passed attributes; the explicit check keeps the return semantics obvious.

### Decision 2: Service naming placement

**Decision**: `App\Services\IdempotencyKeys\LogIdempotencyKey` with a static `execute($key): bool`.

**Rationale**: Matches the repository's existing convention of one static-execute action class per operation under `app/Services/<Domain>/` (e.g. `App\Services\Flags\StoreFlag`).

**Alternatives considered**: A generic `IdempotencyKeyService` with many methods — rejected as unnecessary generalization for a single operation (project principle: simplicity).
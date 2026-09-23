# Data Model: Idempotency Key Entity

**Date**: 2026-09-23
**Feature**: [spec.md](./spec.md)

## Entity: IdempotencyKey

A data record representing a single stored key value. Consumed exclusively by core logic.

### Fields

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | int (bigint) | primary key, auto-increment | Eloquent-standard primary key |
| key | string | required, unique | The key value; non-empty (FR-002), no duplicates (FR-005) |
| created_at | timestamp | auto | Eloquent timestamps |
| updated_at | timestamp | auto | Eloquent timestamps |

### Relationships

None. The entity holds no fields beyond the key value and has no relationships to other entities.

### Validation Rules

- `key` MUST be non-empty (FR-002).
- `key` MUST be unique across all records (FR-005, SC-004).

### State / Lifecycle

- **Created**: a key value is persisted by core logic via `LogIdempotencyKey::execute()`.
- **Deleted**: a record may be removed by core logic; no state transitions beyond existence.
- No update lifecycle: the spec defines persist, retrieve, and delete only.
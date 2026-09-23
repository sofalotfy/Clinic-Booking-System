# Tasks: Idempotency Key Entity

**Input**: Design documents from `/specs/001-indomptency-key/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, quickstart.md

**Tests**: No test tasks are included — tests were not requested in the feature specification. Validation scenarios are documented in quickstart.md.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Laravel web app**: paths under `app/`, `database/` at repository root
- Paths below follow the plan.md structure.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [X] T001 [P] Create `app/Services/IdempotencyKeys/` directory per plan.md structure

**Checkpoint**: Feature directory structure in place.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Data layer that MUST be complete before ANY user story can use the entity

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T002 Create `idempotency_keys` table migration in `database/migrations/2026_09_23_203551_create_idempotency_keys_table.php` with `id`, unique non-empty `key` string, and timestamps per data-model.md
- [X] T003 Run `php artisan migrate` and verify the migration applies cleanly

**Checkpoint**: Foundation ready - the table exists and the model can be created against it.

---

## Phase 3: User Story 1 - Persist a Key Value (Priority: P1) 🎯 MVP

**Goal**: Core logic can persist a key value and is told whether the key is new (`true`) or already logged (`false`).

**Independent Test**: `LogIdempotencyKey::execute('abc')` returns `true` and creates a row; calling it again with `'abc'` returns `false` and does not create a duplicate.

### Implementation for User Story 1

- [X] T004 [US1] Create `IdempotencyKey` model in `app/Models/IdempotencyKey.php` (mass-assignment guard configured)
- [X] T005 [P] [US1] Create `LogIdempotencyKey` service in `app/Services/IdempotencyKeys/LogIdempotencyKey.php` exposing `execute($key): bool`
- [X] T006 [US1] Implement existence check + insert logic in `LogIdempotencyKey::execute()` via `app/Services/IdempotencyKeys/LogIdempotencyKey.php` (returns `false` if `key` already exists, else persists and returns `true`) — depends on T004, T005
- [X] T007 [US1] Guard against empty key values in `app/Services/IdempotencyKeys/LogIdempotencyKey.php` (no empty row persisted) — depends on T006

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently (quickstart Scenario 1, 2, 3).

---

## Phase 4: User Story 2 - Retrieve Stored Keys (Priority: P2)

**Goal**: Core logic can look up stored key values for internal use.

**Independent Test**: Calling `IdempotencyKey::all()` (or equivalent query) returns every persisted key; with no records it returns an empty result.

### Implementation for User Story 2

- [X] T008 [P] [US2] Provide retrieval access to stored keys via Eloquent query on `app/Models/IdempotencyKey.php` (e.g., `IdempotencyKey::all()`), documented for core-logic callers — no new file required

**Checkpoint**: User Stories 1 AND 2 both work.

---

## Phase 5: User Story 3 - Remove a Stored Key (Priority: P3)

**Goal**: Core logic can delete a key no longer needed.

**Independent Test**: Deleting an existing key removes its row; subsequent retrieval does not return it.

### Implementation for User Story 3

- [X] T009 [US3] Provide delete access to key records via Eloquent on `app/Models/IdempotencyKey.php` (e.g., `IdempotencyKey::where('key', $key)->delete()`), documented for core-logic callers — no new file required

**Checkpoint**: All user stories independently functional.

---

## Phase N: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [X] T010 Run `php artisan migrate:fresh` in a test environment and re-run quickstart.md scenarios to validate end-to-end
- [X] T011 [P] Verify no user-facing routes, controllers, or UI were added (feature accessed only by core logic)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3+)**: All depend on Foundational phase completion
  - User stories can proceed in parallel (if staffed)
  - Or sequentially in priority order (P1 → P2 → P3)
- **Polish (Final Phase)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) - No dependencies on other stories
- **User Story 2 (P2)**: Can start after Foundational (Phase 2) - No dependencies on US1 (pure read)
- **User Story 3 (P3)**: Can start after Foundational (Phase 2) - No dependencies on US1/US2 (pure delete)

### Within Each User Story

- Models before services
- Service implementation before validation guard
- Story complete before moving to next priority

### Parallel Opportunities

- T004 and T005 are parallelizable
- User stories 2 and 3 can be implemented in parallel with Story 1 work once T002/T003 complete
- Polish tasks T010 and T011 are parallelizable

---

## Parallel Example: User Story 1

```bash
# Launch model + service skeleton in parallel:
Task: "Create IdempotencyKey model in app/Models/IdempotencyKey.php"
Task: "Create LogIdempotencyKey service skeleton in app/Services/IdempotencyKeys/LogIdempotencyKey.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL - blocks all stories)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: Run quickstart.md Scenario 1-3
5. Deploy/demo if ready

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Test independently → Deploy/Demo (MVP!)
3. Add User Story 2 → Test independently → Deploy/Demo
4. Add User Story 3 → Test independently → Deploy/Demo

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Avoid: vague tasks, same file conflicts, cross-story dependencies that break independence
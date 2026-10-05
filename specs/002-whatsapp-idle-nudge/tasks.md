---

description: "Task list for WhatsApp Idle Conversation Nudge"
---

# Tasks: WhatsApp Idle Conversation Nudge

**Input**: Design documents from `/specs/002-whatsapp-idle-nudge/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: Test tasks ARE included. The spec carries 8 measurable success criteria (SC-001…SC-008),
several of which — SC-003 (a delivery failure never leaves a conversation waiting on a prompt the
user never received), SC-006 (10,000 conversations inside one interval) and SC-008 (no field
re-requested twice per interruption) — are impractical to verify by hand and are natural unit tests.
The repository already has a WhatsApp test suite using `Http::fake()` in `tests/Feature/`. **Strike
the `### Tests` blocks if you want a testless delivery**; no implementation task depends on them.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing.

> ⚠️ **Governance**: `.specify/memory/constitution.md` is unresolved (it documents a FastAPI/Docker/
> pgvector project that is not this repository). Every task below is Laravel-native work derived from
> the spec and codebase, so none of them depend on it. But do not treat that as ratification — see
> plan.md § Constitution Check.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3)
- Include exact file paths

## Path Conventions

Single project: `app/`, `tests/`, `routes/` at repository root.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Verify the environment assumptions the design depends on. This feature adds no
migration and no dependency, so setup is verification rather than scaffolding — but the design's
"no migration needed" claim is load-bearing and should be confirmed, not assumed.

- [X] T001 [P] Verify `whats_app_conversations` has `state`, `step`, `data`, `last_activity_at`, `phone_number`, `user_id`, `doctor_whatsapp_account_id` and that `state` casts to `ConversationState` and `data` to array in `app/Models/WhatsAppConversation.php`. Confirm no migration is needed (data-model.md § Summary)
- [X] T002 [P] Verify the cache store in `.env` (`CACHE_STORE`) supports atomic locks, required by `withoutOverlapping()` for FR-024. `array` does not persist across processes and cannot enforce it
- [X] T003 [P] Verify `SendMessage::buttons(string $phoneNumberId, string $accessToken, string $to, string $text, array $buttons)` is unchanged in `app/APIServices/WhatsApp/SendMessage.php` and that the outbound payload is capped at 3 buttons

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Enum case, handler skeleton and router rewiring. Everything else depends on these.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete.

**Ordering hazard — read before executing.** The enum case `IDLE` is being deleted while two `match`
expressions reference it. Execute T004→T005→T006/T007→T008 in that order: create the replacement
handler *before* the routers point at it, and delete the old handler only *after* both routers are
rewired. Any other order leaves the app with an unhandled enum value or a missing class at some
commit.

- [X] T004 Add `case IDLE_CHECK = 'idle_check';` to `app/Enums/ConversationState.php` (research.md D-002)
- [X] T005 Create `app/APIServices/WhatsApp/States/IdleCheckState.php` with the two static entry points and their exact signatures: `public static function execute(?WhatsAppConversation $conversation, ?array $message = null)` — `$message` MUST be nullable because `ExecutionRouter::execute()` is also the scheduler entry point and has no inbound message (research.md D-011) — and `public static function handleResponse(WhatsAppConversation $conversation, array $message): WhatsAppConversation`. Add private consts for the three reply ids `continue_conversation`, `end_conversation`, `back_to_mainmenu` and the `data` snapshot keys `idle_previous_state`, `idle_prompted_at`. Bodies may be stubs at this point
- [X] T006 Swap the match arm in `app/APIServices/WhatsApp/ConversationRouter.php` (currently line 33): replace `ConversationState::IDLE => IdleState::handleResponse(...)` with `ConversationState::IDLE_CHECK => IdleCheckState::handleResponse(...)` and remove the now-unused `use App\APIServices\WhatsApp\States\IdleState;` import (line 13). Depends on T005
- [X] T007 Swap the match arm in `app/APIServices/WhatsApp/ExecutionRouter.php` (currently line 33): replace `ConversationState::IDLE => IdleState::execute(...)` with `ConversationState::IDLE_CHECK => IdleCheckState::execute(...)` and remove the now-unused `use App\APIServices\WhatsApp\States\IdleState;` import (line 13). Depends on T005
- [X] T008 Delete `app/APIServices/WhatsApp/States/IdleState.php`. Depends on T006 and T007 — the pre-existing handler had no other callers, so removal is closed (FR-025, research.md D-003)
- [X] T009 Fix the stale comment at `app/APIServices/WhatsApp/States/Start.php:12` which reads `// Route non-patient users to IdleState` and now names a deleted class
- [X] T010 Verify zero remaining references: `grep -rn "IdleState\|ConversationState::IDLE" app/` returns nothing, and the project passes `php -l` plus any static analysis the project runs

**Checkpoint**: Enum, handler and routers consistent — the prompt state is reachable but not yet
produced or handled. Nothing user-visible changes yet.

---

## Phase 3: User Story 1 - Resume an interrupted booking (Priority: P1) 🎯 MVP

**Goal**: A conversation abandoned mid-flow receives the Arabic prompt; tapping
`استكمال المحادثة` returns the user to the flow they were in, restarted from its first field.

**Independent Test**: Seed a conversation in an in-progress state with `last_activity_at` backdated,
set `state` to `idle_check` and `data.idle_previous_state` to the target state directly in the fixture,
then drive `IdleCheckState::execute()` and `handleResponse()` and assert the resulting state and
`data`. **Do not depend on the sweep command for this story's tests** — that is US3's work
(T037–T041). That is what keeps US1 independently testable and shippable on its own.

### Tests for User Story 1 ⚠️

> Write these FIRST and confirm they FAIL before implementing.

- [X] T011 [P] [US1] Test: `IdleCheckState::execute()` sends the prompt for an idle in-progress conversation with exactly 3 buttons and a body containing the user's name, in `tests/Feature/IdleConversationNudgeTest.php`
- [X] T012 [P] [US1] Test: `continue_conversation` pops the last entry of `data.callStack` and restores that state in `tests/Feature/IdleConversationNudgeTest.php`
- [X] T013 [P] [US1] Test: `continue_conversation` with an EMPTY `data.callStack` falls back to `data.idle_previous_state` (FR-014) in `tests/Feature/IdleConversationNudgeTest.php` — seed `data` with `idle_previous_state` directly, since writing it is US3's T036
- [X] T014 [P] [US1] Test: resume clears `step`, so a multi-field form restarts at its first field (FR-016, SC-008) in `tests/Feature/IdleConversationNudgeTest.php`
- [X] T015 [P] [US1] Test: resume refreshes `last_activity_at` and clears both snapshot keys (FR-015, SC-002) in `tests/Feature/IdleConversationNudgeTest.php`

### Implementation for User Story 1

- [X] T016 [US1] Implement the prompt body builder in `app/APIServices/WhatsApp/States/IdleCheckState.php`: `اهلا {{name}}` line only when `$conversation->user?->name` is non-null and non-blank, then `هل ترغب بإنhoe المحادثة ؟` always (research.md D-013). Use `users.name` — the only name column in the schema; do NOT fall back to the phone number
- [X] T017 [US1] Implement `execute()` with send-before-persist ordering in `app/APIServices/WhatsApp/States/IdleCheckState.php`: call `SendMessage::buttons(...)` FIRST and write `state = idle_check` only on success. This is load-bearing — `idle_check` is excluded from `active()`, so writing state first would permanently strand the user on any transient HTTP failure (research.md D-007, FR-011, SC-003)
- [X] T018 [US1] Implement the `continue_conversation` branch of `handleResponse()` in `app/APIServices/WhatsApp/States/IdleCheckState.php`, matching the reply id by exact string equality against the const from T005
- [X] T019 [US1] Implement resume state resolution in `app/APIServices/WhatsApp/States/IdleCheckState.php`: pop the last `data.callStack` entry if present, else use `data.idle_previous_state`. The fallback is required, not defensive — `callStack` is empty more often than not in this codebase, and the existing pop in `app/APIServices/WhatsApp/States/InfoConfirmation.php` already tolerates an empty stack
- [X] T020 [US1] Clear `step` and both snapshot keys (`idle_previous_state`, `idle_prompted_at`) and refresh `last_activity_at` on the resume path in `app/APIServices/WhatsApp/States/IdleCheckState.php`. Clearing `step` is what enforces FR-016; leaving it would silently resume mid-form

**Checkpoint**: US1 fully functional and independently testable. A user can be prompted and resume.

---

## Phase 4: User Story 2 - Land on the menu that matches the user (Priority: P2)

**Goal**: Both `إنهاء المحادثة` and `العودة للقائمة الرئيسية` return the user to the menu matching
their audience — patients to the patient menu, doctors and assistants to the staff menu.

**Independent Test**: Seed a conversation in `idle_check` with a resolvable account, invoke
`handleResponse()` with each reply id, assert the resulting state and that `callStack`, `step` and
both snapshot keys are gone. Test each audience separately.

### Tests for User Story 2 ⚠️

- [X] T021 [P] [US2] Test: `end_conversation` for a patient profile resolves to `MAIN_MENU` (FR-017, SC-004) in `tests/Feature/IdleConversationNudgeTest.php`
- [X] T022 [P] [US2] Test: `end_conversation` for a doctor profile and for an assistant profile both resolve to `ADMIN_MENU` (FR-017, SC-004) in `tests/Feature/IdleConversationNudgeTest.php`
- [X] T023 [P] [US2] Test: `back_to_mainmenu` produces an identical outcome to `end_conversation` in `tests/Feature/IdleConversationNudgeTest.php`
- [X] T024 [P] [US2] Test: both menu buttons clear `data.callStack`, `step` and both snapshot keys (FR-018) in `tests/Feature/IdleConversationNudgeTest.php`

### Implementation for User Story 2

- [X] T025 [US2] Implement the `end_conversation` branch in `app/APIServices/WhatsApp/States/IdleCheckState.php` by delegating to `Start::execute($conversation, ['from' => $conversation->phone_number])`, reusing the audience branch already at `app/APIServices/WhatsApp/States/Start.php:13-21` rather than duplicating it. The synthesized message is required because `Start::execute` reads `$message['from']` and types it non-nullable while a scheduled run has no inbound message (research.md D-010)
- [X] T026 [US2] Implement the `back_to_mainmenu` branch in `app/APIServices/WhatsApp/States/IdleCheckState.php` by delegating to the SAME code path as T025. The two buttons are behaviourally identical; no code path may treat one differently from the other
- [X] T027 [US2] Clear both snapshot keys on the menu path in `app/APIServices/WhatsApp/States/IdleCheckState.php`, then confirm `Start::execute` already clears `step` and `data` (FR-018). Only add explicit clearing here if it does not

**Checkpoint**: US1 AND US2 work independently. Every prompt button now has a defined outcome.

---

## Phase 5: User Story 3 - Interrupt only productive flows, and only once (Priority: P3)

**Goal**: A sweep identifies genuinely abandoned productive conversations, prompts each exactly once,
and reports what it did.

**Independent Test**: Invoke `php artisan app:check-idle-conversations --minutes=0` against fixtures
with `Http::fake()`, asserting `Http::assertSentCount()` and the reported counts.

### Tests for User Story 3 ⚠️

- [X] T028 [P] [US3] Test: `active()` excludes `START`, `MAIN_MENU`, `ADMIN_MENU` and `IDLE_CHECK`, and includes productive states (clarification Q1) in `tests/Feature/IdleConversationNudgeTest.php`
- [X] T029 [P] [US3] Test: the sweep prompts a conversation that is in an in-progress state AND past the idle window, and skips it when EITHER clause of FR-002 fails — specifically: skip when the state is a menu but the timestamp is old, and skip when the state is in-progress but the timestamp is fresh. Both regression directions are quiet failures worth pinning (quickstart.md § 3)
- [X] T030 [P] [US3] Test: a conversation already in `idle_check` is never re-prompted on a second run (FR-012, SC-002) in `tests/Feature/IdleConversationNudgeTest.php`
- [X] T031 [P] [US3] Test: a failed send leaves `state` unchanged so the conversation is retried on the next run (FR-011, SC-003) in `tests/Feature/IdleConversationNudgeTest.php`
- [X] T032 [P] [US3] Test: the command reports prompted, skipped and failed counts to stdout (FR-022, SC-007) in `tests/Feature/IdleConversationNudgeTest.php`
- [X] T033 [P] [US3] Test: a null or blank `users.name` omits the greeting line but still sends the question (research.md D-013) in `tests/Feature/IdleConversationNudgeTest.php`
- [X] T034 [P] [US3] Test: a conversation with no usable `doctor_whatsapp_accounts` record is counted as failed without aborting the remaining batch (research.md D-014) in `tests/Feature/IdleConversationNudgeTest.php`

### Implementation for User Story 3

- [X] T035 [US3] Add `public const array ROUTING_STATES = [self::START, self::MAIN_MENU, self::ADMIN_MENU, self::IDLE_CHECK];` and `public static function active(): array` to `app/Enums/ConversationState.php`, implemented as `self::cases()` filtered to exclude `ROUTING_STATES` so a state added later is picked up automatically (research.md D-001). Do NOT hard-code a list of productive states — that fails silently when a new flow is added
- [X] T036 [US3] Write the snapshot keys `idle_previous_state` (the state being replaced) and `idle_prompted_at` onto `data` in `app/APIServices/WhatsApp/States/IdleCheckState.php` execute(), in the same write as the `idle_check` transition. Both or neither — a half-written snapshot makes FR-014 fall back to a state the conversation was never in. Preserve all other existing `data` keys
- [X] T037 [US3] Create `app/Console/Commands/CheckIdleConversations.php` with `protected $signature = 'app:check-idle-conversations {minutes=10}'`, validating `minutes` as a non-negative integer and returning `self::FAILURE` on a bad value (contracts/cli-command.md)
- [X] T038 [US3] Implement the selector in `app/Console/Commands/CheckIdleConversations.php`: `whereIn('state', ConversationState::active())` AND `where('last_activity_at', '<=', now()->subMinutes($this->minutes))` — BOTH clauses are required (FR-002) — with `->with(['user', 'doctorWhatsAppAccount'])` and iterated by `chunkById(500, ...)` rather than offset-based `chunk`, since rows are mutated to `idle_check` mid-iteration and offset pagination can skip them (research.md D-005)
- [X] T039 [US3] Implement per-conversation exception isolation and the three counters in `app/Console/Commands/CheckIdleConversations.php`: catch per row, increment `failed`, log the conversation id and reason, and **continue** so one bad recipient never aborts the batch. Derive `skipped` as `examined - prompted - failed` so the three counts sum to the candidates examined. Return `self::FAILURE` when `failed > 0` (FR-023) — the sweep still completes before the verdict is rendered (research.md D-014)
- [X] T040 [US3] Emit the counts line to stdout in `app/Console/Commands/CheckIdleConversations.php` as `Idle check: {n} prompted, {n} skipped, {n} failed (window {n}m)`, readable without inspecting logs (FR-022, SC-007)
- [X] T041 [US3] Register the schedule in `routes/console.php` as `Schedule::command('app:check-idle-conversations')->everyMinute()->withoutOverlapping();` — `withoutOverlapping()` is what satisfies FR-024 and must not be dropped (research.md D-006)

**Checkpoint**: All three stories independently functional. The feature is complete end to end.

---

## Phase 6: Polish & Cross-Cutting Concerns

- [X] T042 [P] Run `./vendor/bin/pint` on the changed files only — the branch has pre-existing style violations, so compare against the baseline rather than expecting a clean run
- [X] T043 [P] Run `php artisan test` and confirm the failure count is unchanged from baseline (3 pre-existing WhatsApp failures: `AppointmentReminderTest`, `NotificationTest`/`InfoConfirmationTest`). Do not assume a clean baseline
- [X] T044 [P] Run `php artisan schedule:list` and confirm `app:check-idle-conversations` is registered at a `* * * * *` cadence
- [X] T045 [P] Walk through quickstart.md § "Validate end to end against a real account" with a non-production Meta test number, confirming the prompt text and all three button outcomes
- [X] T046 [P] Confirm any tooling that enumerates conversation state values by hand has `idle_check` added (contracts/whatsapp-messages.md § 3)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — verification only, can start immediately
- **Foundational (Phase 2)**: Depends on Setup. **BLOCKS all user stories**
- **User Stories (Phase 3–5)**: All depend on Foundational. Can proceed in parallel or in priority order
- **Polish (Phase 6)**: Depends on all desired stories

### User Story Dependencies

- **US1 (P1)**: After Foundational. No dependency on US2 or US3
- **US2 (P2)**: After Foundational. No dependency on US1 or US3
- **US3 (P3)**: After Foundational. No dependency on US1 or US2

**All three stories are genuinely independent.** US1 and US2 are tested by driving
`IdleCheckState` directly against a conversation already parked in `idle_check`, never by running the
sweep. That is deliberate: it lets US1 ship as an MVP without US3 existing, and it is why T013 seeds
`idle_previous_state` in the fixture instead of relying on T036.

**One soft dependency to respect**: T036 writes the snapshot that US1's fallback reads. If US3 is
implemented after US1, US1's fallback test must seed the snapshot by hand. Do not "fix" this by
adding a cross-story dependency — that would break the MVP boundary.

### Within Each User Story

- Tests FIRST, and confirm they fail before implementing
- Models before services before handlers
- Core implementation before integration
- Within Foundational: T005 before T006/T007 before T008 (deletion last)

### Parallel Opportunities

- All 3 Setup tasks marked [P] — different concerns, no file overlap
- T006 and T007 in Foundational are parallel (different router files)
- T011–T015 (US1 tests), T021–T024 (US2 tests), T028–T034 (US3 tests) are each internally parallel
- T016–T020 (US1) and T025–T027 (US2) both touch `IdleCheckState.php` — **not** parallel with each
  other, only with US3's T035/T041
- All Polish tasks marked [P]

---

## Parallel Example: User Story 1

```bash
# Launch all US1 tests together (after T005, before T016):
Task: "Test: IdleCheckState::execute() sends the prompt with 3 buttons and the name"
Task: "Test: continue_conversation pops the last entry of data.callStack"
Task: "Test: continue_conversation with an EMPTY data.callStack falls back to idle_previous_state"
Task: "Test: resume clears step so a multi-field form restarts at its first field"
Task: "Test: resume refreshes last_activity_at and clears both snapshot keys"
```

```bash
# Foundational: these two are safe to run simultaneously (different files):
Task: "Swap the match arm in app/APIServices/WhatsApp/ConversationRouter.php"
Task: "Swap the match arm in app/APIServices/WhatsApp/ExecutionRouter.php"
```

## Parallel Example: User Story 3

```bash
# Launch all US3 tests together:
Task: "Test: active() excludes START, MAIN_MENU, ADMIN_MENU and IDLE_CHECK"
Task: "Test: the sweep prompts only when BOTH clauses of FR-002 hold"
Task: "Test: a conversation already in idle_check is never re-prompted"
Task: "Test: a failed send leaves state unchanged so the next run retries"
Task: "Test: the command reports prompted, skipped and failed counts"
Task: "Test: a null or blank users.name omits the greeting line"
Task: "Test: a missing whatsapp account is counted as failed without aborting the batch"
```

---

## Implementation Strategy

### MVP First (User Story 1 only)

1. Phase 1: Setup (verification, ~15 min)
2. Phase 2: Foundational — **respect the T005→T006/T007→T008 ordering**
3. Phase 3: User Story 1
4. **STOP and VALIDATE** against US1's independent test criteria
5. Do **not** schedule anything yet — without T041 the feature is inert, and without T036 the
   FR-014 resume fallback has nothing to fall back to in production

> An MVP of US1 alone is demonstrable but **not deployable**: the sweep that creates the prompt is
> US3's T037–T041. Plan to ship US1 + US3 together as the first deployable increment. US2 can follow.

### Incremental Delivery

1. Setup + Foundational → foundation consistent
2. US1 → test independently → demonstrable
3. **US3** → adds the sweep → **first genuinely deployable increment**
4. US2 → completes the button set
5. Polish → lint, full suite, live walkthrough

### Parallel Team Strategy

With two developers:

1. Both complete Setup + Foundational together (the enum/router/handler swap needs one owner)
2. Then:
   - Developer A: **US1 and US2** — sequential, both edit `IdleCheckState.php`
   - Developer B: **US3** — `ConversationState.php`, the new command, `routes/console.php`
   - The only shared file is `IdleCheckState.php`, and US3 touches it solely in T036 (snapshot write),
     which is additive and lands after T020
3. Merge US3's T036 last to avoid conflict

---

## Notes

- [P] tasks = different files, no dependencies
- Each user story is independently completable and testable
- Verify tests fail before implementing
- Commit after each task or logical group; **Foundational must not be split across commits**
- Stop at any checkpoint to validate the story independently
- Highest-risk tasks, in order: **T008** (deleting the handler), **T017** (send-before-persist),
  **T038** (both FR-002 clauses), **T041** (not dropping `withoutOverlapping`)
- Avoid: hard-coding the active-state list (T035), offset-based chunking (T038), widening all ~20
  state handlers' `$message` types instead of only the new one (T005)
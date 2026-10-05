# Quickstart: Validating the WhatsApp Idle Conversation Nudge

**Phase**: 1 (Design) | **Date**: 2026-10-05
**Input**: [spec.md](./spec.md), [plan.md](./plan.md), [data-model.md](./data-model.md),
[contracts/](./contracts/)

A runnable guide for proving the feature works end to end. It describes *how to verify*, not what to
implement — the implementation detail belongs in `tasks.md`.

---

## Prerequisites

| Requirement | Check | Note |
|---|---|---|
| PHP 8.3+ | `php -v` | composer constraint is `^8.3` |
| Composer deps installed | `composer install` | adds no new dependencies for this feature |
| MySQL reachable | `php artisan migrate:status` | or use the test suite's in-memory SQLite |
| Cache store supports locks | see note below | required by `withoutOverlapping()` |
| Meta Cloud API credentials present | see `.env` | only needed for a real send |

**Lock support note.** `withoutOverlapping()` is implemented with a cache lock. Laravel's `database`
and `redis` cache stores support atomic locks; the `array` store does not persist across processes, so
it cannot enforce non-overlap across separate `schedule:run` invocations. Check `CACHE_STORE` in
`.env` before relying on FR-024 in a deployed environment.

## Setup

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan storage:link   # only if the local app serves anything over HTTP
```

For test runs no MySQL or Cloud API access is needed — the suite uses an in-memory database and
`Http::fake()`.

---

## Validate the pieces

### 1. `active()` returns the routing complement

```bash
php artisan tinker --execute='print_r(array_map(fn($s) => $s->value, App\Enums\ConversationState::active()));'
```

**Expected**: every conversation state **except** chat start, the patient menu, the staff menu, and the
new prompt state (`idle_check`). Two specific checks:

- `App\Enums\ConversationState::IDLE` must no longer exist at all (removed — FR-025). Referencing it
  should be a fatal/analysis error, not `false`.
- `idle_check` must **not** appear in the output. Its absence is what guarantees an ignored prompt is
  never re-sent (FR-012).

### 2. The schedule is registered and non-overlapping

```bash
php artisan schedule:list
```

**Expected**: `app:check-idle-conversations` listed at a `* * * * *` cadence. Confirm
`withoutOverlapping` is present in `routes/console.php` — `schedule:list` will not show it.

### 3. Selector correctness — the most important cheap test

With a seeded conversation sitting in an in-progress state, confirm **both** clauses of FR-002 apply,
and that neither alone is enough:

```bash
php artisan app:check-idle-conversations --minutes=0
```

| Setup | Expected |
|---|---|
| in-progress state, `last_activity_at` = 10 min ago, `--minutes=0` | prompted |
| in-progress state, `last_activity_at` = now, `--minutes=10` | skipped |
| `MAIN_MENU`, `last_activity_at` = 1 day ago | skipped (state, not time, is disqualifying) |
| `idle_check`, `last_activity_at` = 1 day ago | skipped — no second prompt, ever |

The third and fourth rows are the regression cases. A selector that checks only
`last_activity_at` prompts people who are simply sitting at a menu; one that checks only `state`
prompts conversations the user just used.

Against `Http::fake()` these runs produce no outbound calls. Against a real account they would message
a real person — **use `--minutes=0` only against fixtures, never against production data.**

### 4. Idempotency

Run the sweep twice against the same fixture:

```bash
php artisan app:check-idle-conversations --minutes=0
php artisan app:check-idle-conversations --minutes=0
```

**Expected**: the second run reports `prompted=0` for the already-prompted conversation, because
`idle_check` is excluded from `active()`. No duplicate message is sent. This confirms the spec
assumption that running more often than once a minute is harmless.

### 5. The test suite

```bash
php artisan test --filter=IdleConversationNudge
```

The scenarios the test file must cover — this is a coverage checklist, not the suite itself:

| # | Scenario | Requirement |
|---|---|---|
| 1 | Idle conversation in an in-progress state receives the prompt with 3 buttons and the name | FR-001, FR-002 |
| 2 | `continue_conversation` resumes the flow named by the top of `callStack` | FR-013 |
| 3 | `continue_conversation` with an **empty** `callStack` falls back to `idle_previous_state` | FR-014 |
| 4 | Resume clears `step`, so a multi-field form restarts at its first field | FR-016, SC-008 |
| 5 | Resume refreshes `last_activity_at`, so no immediate second prompt | FR-015, SC-002 |
| 6 | `end_conversation` as a patient → patient menu; as a doctor/assistant → staff menu | FR-017, SC-004 |
| 7 | `back_to_mainmenu` behaves identically to `end_conversation` | FR-017 |
| 8 | Both menu buttons clear `callStack`, `step` and the snapshot keys | FR-018 |
| 9 | Free-text reply re-sends the prompt and leaves the state at `idle_check` | FR-020 |
| 10 | Send failure leaves the row eligible — state **not** written, retried next run | FR-011, SC-003 |
| 11 | Conversation with a null/blank `name` sends the question with no greeting line | FR-001, D-013 |
| 12 | Exactly 3 buttons in the outbound payload | platform limit |

Full suite, to catch regressions outside the feature:

```bash
php artisan test
```

> Known pre-existing failures on this branch, unrelated to this feature: 3 WhatsApp tests
> (`AppointmentReminderTest`, `NotificationTest`/`InfoConfirmationTest`) were failing before this work.
> Confirm the failure count is unchanged rather than assuming a clean baseline.

`./vendor/bin/pint` also reports pre-existing style violations on this branch; run it and check only
that this feature's files are clean.

---

## Validate end-to-end against a real account

Only with a non-production Meta test number.

```bash
php artisan tinker
```

1. Simulate a user starting a booking flow and abandoning it mid-form.
2. Backdate the activity so the window has elapsed:
   ```php
   $c = App\Models\WhatsAppConversation::latest('id')->first();
   $c->update(['last_activity_at' => now()->subMinutes(11)]);
   ```
3. Trigger the sweep without waiting for the scheduler:
   ```bash
   php artisan app:check-idle-conversations
   ```
4. **Expected**: the recipient receives a message beginning `اهلا <name>` and
   `هل ترغب بإنهاء المحادثة ؟` with three buttons. `prompted=1`.

Then, replying on the phone as the user:

| Tap | Expected |
|---|---|
| `استكمال المحادثة` | the interrupted flow restarts at its **first** field |
| `إنهاء المحادثة` | patient menu for a patient, staff menu for a doctor/assistant |
| `العودة للقائمة الرئيسية` | identical to the above |
| any free text | the prompt arrives again; no state change |

Re-run the sweep after each reply and confirm the counts read `prompted=0` — the conversation is back
in an in-progress or menu state with a fresh `last_activity_at`.

---

## Rollback

The feature is fully reversible with no data migration:

```bash
git revert <implementation commit>
php artisan schedule:clearCache   # only if you have a persistent scheduler cache
```

Conversations left in `idle_check` by a deployed version would have no handler after a revert, so
drain that state before downgrading:

```php
App\Models\WhatsAppConversation::where('state', 'idle_check')
    ->update(['state' => 'main_menu', 'data' => null]);
```

Any other pre-existing state value is equally valid here — the point is only to move rows out of a
state that will no longer be handled. Do this on a copy first and confirm the row count first.
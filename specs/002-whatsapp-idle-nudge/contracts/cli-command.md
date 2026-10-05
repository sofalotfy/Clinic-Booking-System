# Contract: `app:check-idle-conversations`

**Kind**: CLI (Artisan command)
**Provided by**: `app/Console/Commands/CheckIdleConversations.php`
**Invoked by**: the scheduler (`routes/console.php`) and by developers / tests

## Signature

```text
php artisan app:check-idle-conversations [--minutes=10]
```

## Arguments and options

| Name | Type | Required | Default | Meaning |
|---|---|---|---|---|
| `--minutes` | integer ≥ 0 | no | `10` | Idle window in minutes. Matches the conversation expiry applied by `ConversationManager`. |

`--minutes` exists so a developer or the test suite can exercise the sweep without waiting ten
minutes (spec Assumptions). It is not a user-facing setting, and adding a configurable window is
out of scope.

- `--minutes=0` — prompts every conversation currently in an in-progress state, including one
  messaged this instant. Useful for verifying selection logic in a fixture.
- `--minutes=-1` — not a supported use. Validate as a non-negative integer and fail fast rather than
  silently selecting nothing or everything.

## Exit codes

| Code | Constant | Condition |
|---|---|---|
| `0` | `self::SUCCESS` | Every candidate conversation was prompted |
| `1` | `self::FAILURE` | The `--minutes` value was invalid, **or** at least one conversation failed to prompt (FR-023) |

**A non-zero exit on per-conversation failure is required, not incidental.** FR-023 states the run
"MUST report overall failure when at least one conversation failed, so that an operator or monitoring
system can detect partial outages". Treat the exit code as the outage signal.

This does not conflict with per-conversation isolation: a failed conversation is caught, logged and
counted, and the sweep **continues** through the rest of the batch. The verdict is rendered only
after every candidate has been processed, so one bad recipient never discards the other thousands'
work — it only sets the final exit code.

Consequence for operators: `schedule:run` will report the job as failed on any minute that contains
at least one delivery failure. The counts line distinguishes a one-off from a sustained outage
(a large `failed` figure), so the exit code is a trigger to read the counts, not a substitute for them.

## Output contract

FR-022 requires prompted, skipped and failed counts to be determinable from the run's output alone
(SC-007). Report each run on a single line to stdout:

```text
Idle check: 42 prompted, 1,203 skipped, 2 failed (window 10m)
```

| Count | Definition |
|---|---|
| `prompted` | Candidates that transitioned to `idle_check` because the send succeeded |
| `skipped` | Candidates examined but neither prompted nor failed — `examined - prompted - failed`. Normally zero, since the selector pre-filters; non-zero only if a send returns truthy without prompting |
| `failed` | Candidates whose send threw or returned unsuccessful; each is logged individually with its conversation id and reason |

The three counts sum to the number of candidates examined. Candidates excluded by the selector
(menus, already-prompted conversations, conversations not yet idle) are **not** counted — they were
never examined, and reporting them as "skipped" would make the number reflect table size rather than
anything the run decided.

Counts are informational. The exit code and the counts are independent: `prompted=0, failed=0` is a
normal, successful, empty run.

Failed conversations remain eligible for the next run, because their state was never written to
`idle_check` (FR-011).

## Scheduling contract

Registered in `routes/console.php` as:

```text
Schedule::command('app:check-idle-conversations')->everyMinute()->withoutOverlapping();
```

- `everyMinute()` — the interval is fixed by the feature (one run per minute).
- `withoutOverlapping()` — FR-024 forbids concurrent runs. A run overrunning its minute is skipped
  rather than started a second time. Implemented with a cache lock, so the cache driver's `lock`
  support must be available (see quickstart.md, note on drivers).
- The scheduler host must run `php artisan schedule:run` every minute; the feature adds the schedule
  declaration but not the OS-level cron entry.

## Preconditions

| Precondition | If unmet |
|---|---|
| `whats_app_conversations` exists with `state`, `last_activity_at`, `phone_number`, `data` | command fails; exit `1` |
| Each qualifying conversation has a usable `doctor_whatsapp_accounts` record | that conversation is **failed**, not fatal; see D-014 |
| Cache store supports atomic locks (for `withoutOverlapping`) | schedule entry must be adjusted; the command itself still runs |

## Idempotency

Re-running is safe and produces no duplicate prompts. Enforced by the selector, not by a marker:
once a conversation is in `idle_check` — which is not in `active()` — it no longer matches the query.
Only a genuinely new interruption (a reply returning it to an in-progress state, followed by another
idle window) makes it eligible again. This satisfies the spec assumption that running the check more
often than once a minute is harmless.
# Research: WhatsApp Idle Conversation Nudge

**Phase**: 0 (Outline & Research) | **Date**: 2026-10-05
**Input**: [spec.md](./spec.md), [plan.md](./plan.md)

All `NEEDS CLARIFICATION` markers in the plan's Technical Context are resolved below. Nothing in
this document contradicts the specification; where a decision adds implementation detail the spec
deliberately left open, the spec is unchanged and the detail lives here.

---

## D-001 — `active()` is a self-maintaining complement, not an explicit list

**Decision**: `ConversationState::active(): array` returns
`array_values(array_filter(self::cases(), fn (self $s) => !in_array($s, self::ROUTING_STATES, true)))`,
where `ROUTING_STATES` is `public const array ROUTING_STATES = [self::START, self::MAIN_MENU, self::ADMIN_MENU, self::IDLE_CHECK]`.

**Rationale**: The user asked for "a method called active that would return an array of the specified
states", and the chosen definition of those states was *the complement of the routing states*. The
feature's whole purpose is to catch conversations abandoned in **any** productive flow. A literal
hard-coded list of ~15 enum cases would mean a new flow added later is silently never prompted until
somebody remembers to edit a second place — a quiet failure that degrades into "our new booking flow
never gets nudges" with no error anywhere.

**Alternatives considered**:
- *Hard-coded list of every productive state.* Rejected: duplicates the enum as the source of truth and
  fails open-silently when a state is added.
- *Enum method returning case values (strings) instead of cases.* Rejected: callers must cast again;
  returning cases keeps type safety at the call site.
- *Excluding dead states too* (`EMERGENCY_CASE_IN_HOME`, `EMERGENCY_CASE_IN_HOSPITAL`, the two
  notification states). Deferred — see D-012.

---

## D-002 — New state is `IDLE_CHECK`

**Decision**: Add `case IDLE_CHECK = 'idle_check';` to `ConversationState`.

**Rationale**: The clarification session established there must be exactly one state meaning "asked
the user whether to end the chat" (Q7). The pre-existing `IDLE` case was chosen as that state,
described in code as a placeholder that `Start` routes to "non-patient users" and whose handler
exists purely to reply with an English greeting. Reusing it would have been cheaper but the spec
FR-025 requires removing it, and reusing it also inherits its English message and non-nullable
message parameter. `IDLE_CHECK` reads unambiguously next to `IDLE` in a diff, which matters during
the swap.

**Alternatives considered**:
- *Reuse and repurpose `IDLE`.* Rejected by Q7; also drags along the English text and the
  `Start` mis-wiring.
- *`CONVERSATION_IDLE` / `AWAITING_IDLE_DECISION`.* Rejected as no clearer than the above; the name is
  internal and the user-facing contract is the button set, not the constant name.

---

## D-003 — Remove `IdleState` completely, including its `Start.php` comment

**Decision**: Delete `app/APIServices/WhatsApp/States/IdleState.php`, remove the `use` import and
the `ConversationState::IDLE` match arm from **both** `ConversationRouter.php:33` and
`ExecutionRouter.php:33`, and fix the stale comment at `Start.php:12` that reads
`// Route non-patient users to IdleState`.

**Rationale**: FR-025 mandates removal, and a match arm in a `match` expression over an enum is
exhaustiveness-checked by static analysis — leaving one behind for a deleted case is a hard error,
not a runtime surprise. `IdleState` had no other callers (verified: 7 references total, all accounted
for), so removal is closed.

**Alternatives considered**:
- *Leave the class, unreferenced.* Rejected: dead code that contradicts FR-025.
- *Only delete the enum case.* Impossible: both routers would fatal on an unhandled enum value.

---

## D-004 — New command `app:check-idle-conversations {minutes=10}`

**Decision**: One Artisan command, signature `app:check-idle-conversations {minutes=10}`, returning
`self::SUCCESS` / `self::FAILURE`.

**Rationale**: `minutes` is exposed as an option purely so a developer and the test suite can exercise
the feature without waiting ten minutes; the spec records this as an assumption ("overridable per run
for testing purposes without being configurable in normal operation"). A single command rather than
one command per audience, because the query filters on state, not on profile.

**Alternatives considered**:
- *Hard-coding the window to a config value.* Rejected: adds a config file and a settings surface the
  spec puts out of scope, for a number that must stay aligned with `ConversationManager`'s existing
  10-minute expiry.
- *A queued job instead of a command.* Rejected: SC-006 requires the whole sweep to finish within one
  60-second interval; queue dispatch adds a worker dependency for no benefit at this size.

---

## D-005 — Selection query, eager-loaded and chunked by primary key

**Decision**: In the command, query
`WhatsAppConversation::query()->whereIn('state', ConversationState::active())->where('last_activity_at', '<=', now()->subMinutes($this->minutes))`
with `->with(['user', 'doctorWhatsAppAccount'])`, iterated with `->chunkById(500, ...)`.

**Rationale**: `whereIn('state', ...)` against a value list is index-usable on `whats_app_conversations.state`;
adding the `last_activity_at` ceiling turns the sweep into a bounded range scan rather than a full
table scan. `chunkById` (not `chunk`) keeps the cursor stable while rows are being updated to
`idle_check` underneath it — with offset-based `chunk`, mutating the `state` column mid-iteration can
shift row offsets and skip conversations. Eager loading both relations matters because each prompt needs
the recipient's name and a per-doctor access token; without it, 10,000 prompts become 20,000 extra
queries and blow the one-minute budget.

**Alternatives considered**:
- *Offset-based `chunk(500)`.* Rejected: skipping rows due to in-place mutation is a real risk here.
- *`lazyById`.* Rejected: same keyset property as `chunkById` but unbounded query count, so a bad
  `minutes` argument could pull the whole table one row at a time.
- *Filtering on `expires_at` instead of `last_activity_at`.* Rejected: `expires_at` is set to
  `last_activity_at + 10 min` per inbound message, so it encodes the same information but is a
  derived column whose semantics are less obvious at the call site; and when `minutes` is overridden
  for testing, `expires_at` would still reflect the fixed 10 minutes.

---

## D-006 — Overlap prevention via `withoutOverlapping()`

**Decision**: Schedule with `->everyMinute()->withoutOverlapping()`.

**Rationale**: FR-024 forbids overlapping runs. `withoutOverlapping()` is the framework's own answer,
implemented with a cache lock, and it composes with `everyMinute()` — a run that overruns its interval
is skipped rather than started a second time. Enforcing it inside the command instead would need a
manual lock with its own expiry bookkeeping and would still race with the scheduler.

**Alternatives considered**:
- *`onOneServer()`.* Rejected: that is about cross-host coordination; a single scheduler host does not
  need it and it would require a shared cache store.
- *Checking for a running process inside the command.* Rejected: reinventing the lock, less portable.

---

## D-007 — Prompt the state is persisted only after a successful send

**Decision**: `IdleCheckState::execute()` calls `SendMessage::buttons(...)` first and only then writes
`state = idle_check` plus the resume snapshot.

**Rationale**: This is the highest-consequence ordering decision in the feature, and it falls directly
out of FR-011 combined with D-001. Because `idle_check` is excluded from `active()`, a conversation is
*never re-examined once it is in that state*. Writing the state first would mean a transient HTTP
timeout silently converts "conversation abandoned mid-booking" into "conversation silently dead
forever" — the user is still stuck, but no longer recoverable by any later run. Sending first means a
failure leaves the row eligible, so the next minute retries it.

**Alternatives considered**:
- *Write state first, then send.* Rejected as above.
- *Record the failure in a separate retry column.* Rejected: an unnecessary schema change for a case
  the next run already handles correctly.

---

## D-008 — Pre-interruption state is snapshotted into `data`

**Decision**: On a successful prompt, store in the `data` JSON:
`idle_previous_state` (the `value` of the state the conversation was in when prompted) and
`idle_prompted_at` (timestamp). Both are cleared when any prompt button is handled.

**Rationale**: FR-013 resumes by popping the conversation's pending-step sequence (`data.callStack`,
maintained in `BookAppointment.php` and `ManageAppointment.php` and popped in `InfoConfirmation.php`),
and FR-014 requires falling back to the step recorded before the interruption. That fallback state has
to be captured at prompt time — once `state` is overwritten with `idle_check`, the previous value is
gone. A timestamp is stored alongside it purely as an operational breadcrumb (how long a user sat on
the prompt), and is also the natural key for the "already prompted" audit described in FR-012.

**Alternatives considered**:
- *Infer the previous state from `data.callStack` alone.* Rejected: the spec explicitly requires the
  fallback, and `callStack` is empty more often than not in this codebase — the explore pass found
  `InfoConfirmation`'s own pop tolerating an empty stack.
- *A dedicated nullable `previous_state` column.* Rejected: would require a migration for a value that
  is inherently transient, and `data` already exists for exactly this kind of per-flow scratch state.

---

## D-009 — Resume clears the snapshot; menu buttons clear flow data too

**Decision**: On `continue_conversation`: pop `data.callStack`, use the popped value or
`data.idle_previous_state` as the restored state, then unset both snapshot keys. On `end_conversation`
and `back_to_mainmenu`: unset `callStack`, `idle_previous_state`, `idle_prompted_at` and `step`, then
dispatch to the audience menu.

**Rationale**: FR-016 is deliberately strict — resuming restarts the flow from its *first* field and
discards partially entered values. Clearing `step` is what actually enforces that; leaving `step` at
its interrupted value would resume mid-form and silently violate the user's chosen behaviour. FR-018
independently requires discarding partial progress on the two menu buttons, and FR-013 leaves the
menu buttons untouched, so both paths reset.

**Alternatives considered**:
- *Preserving `step` on resume so users don't retype.* Rejected: that was the explicitly rejected
  alternative to Q1 in the clarification session; the trade-off was accepted to avoid carrying more
  per-conversation state.
- *Keeping the snapshot after resuming.* Rejected: it would be stale the moment the conversation changes
  state again, and `data` is persisted.

---

## D-010 — Reuse `Start::execute` for the audience menu, with a synthesized message

**Decision**: Both menu buttons call `Start::execute($conversation, ['from' => $conversation->phone_number])`.

**Rationale**: The patient-versus-staff branch the user asked for already exists at
`Start.php:13-21` and is exactly the right logic. Reusing it keeps a single source of truth for
"which menu belongs to whom" — duplicating the branch into `IdleCheckState` would be two places to
keep in sync if a third audience is ever added. The synthesized message is required because
`Start::execute` and `MainMenu::execute` both read `$message['from']` and type the parameter as a
non-nullable `array`, while a scheduled run has no inbound message to hand them. `Start::execute` also
clears `step` and `data`, which satisfies the FR-018 reset requirement for free.

**Alternatives considered**:
- *Calling `MainMenu::execute` / `AdminMenu::execute` directly behind an audience check.* Rejected:
  duplicates the branch; `AdminMenu` reads `$conversation->phone_number` while `MainMenu` reads
  `$message['from']`, so the two paths are asymmetric and easy to get wrong.
- *Making the `$message` parameters nullable across all states.* Rejected: a wide behavioural change
  across ~20 classes, well beyond this feature's scope. Handled narrowly in D-011 instead.

---

## D-011 — `IdleCheckState::execute()` declares `$message` nullable

**Decision**: Both `execute(?WhatsAppConversation $conversation, ?array $message = null)` and
`handleResponse(WhatsAppConversation $conversation, array $message): WhatsAppConversation`.

**Rationale**: `ExecutionRouter::execute($conversation, ?array $message = null)` is the cron entry
point, and the pre-existing `IdleState::execute` typed its parameter as a non-nullable `array`. The
scheduled command has no inbound message, so a non-nullable declaration raises a `TypeError` on
every run. Declaring it nullable on the new handler is the minimal correct fix. `handleResponse` stays
non-nullable because it is only ever reached from an actual inbound reply.

**Alternatives considered**:
- *Passing a dummy `[]` from the cron path.* Rejected: it would satisfy the type but feed a handler
  designed for real replies a fake input, and any future `$message['...']` read becomes a warning.
- *Widening every state handler to nullable.* Rejected: unnecessary churn across the whole router.

---

## D-012 — Dead states remain inside `active()`

**Decision**: Do not exclude `EMERGENCY_CASE_IN_HOME`, `EMERGENCY_CASE_IN_HOSPITAL`, or the two
notification states, even though the explore pass found no inbound transitions into the first two.

**Rationale**: The clarification session answered the eligible-state question as a complement of the
routing states. Excluding additional states is a scope change the user did not ask for, and these are
arguably *not* dead — a conversation abandoned mid-emergency-triage is precisely the case a user most
wants to recover. Flagged here rather than silently acted on.

**Alternatives considered**:
- *Exclude states with no inbound transition.* Rejected: absence of a transition today is not proof of
  absence, and the cost of over-prompting is one message versus permanently stranding an emergency flow.

---

## D-013 — Unresolved: what to send when the recipient's name is unknown

**Decision**: `users.name` is the only name column in this schema and it is nullable. If
`$conversation->user?->name` is null or blank, send the question line alone (no greeting line);
otherwise send `اهلا {name}` followed by `هل ترغب بإنهاء المحادثة ؟`.

**Rationale**: FR-001 says the prompt names the user "where a name is available", which the spec
deliberately qualified rather than requiring a name it cannot guarantee. Emitting `اهلا ` with a
trailing blank reads as a bug to a recipient; dropping the line is graceful. Not escalated as a
clarification because the spec already covers it.

**Alternatives considered**:
- *Falling back to the phone number.* Rejected: reading a raw number aloud in the greeting is worse
  than omitting it.
- *Falling back to the doctor's/clinic's name.* Rejected: the spec's greeting addresses the user.

---

## D-014 — Per-conversation failures are isolated, but the run still reports failure

**Decision**: The command catches per-conversation exceptions inside the chunk loop, increments a
`failed` counter, logs the conversation id and reason, and continues so one broken phone number never
aborts the remaining batch. The command then returns `self::FAILURE` when `failed > 0`.

**Rationale**: Two distinct requirements are in tension and both must hold. FR-020/the selector need
per-conversation isolation — aborting a 10,000-row sweep on the first bad recipient is unacceptable.
FR-023 separately requires the *run* to report overall failure when at least one conversation failed,
"so that an operator or monitoring system can detect partial outages". Returning `SUCCESS`
unconditionally would satisfy isolation while silently discarding the outage signal.

> **Correction.** An earlier draft of this decision returned `self::SUCCESS` even when conversations
> failed, on the reasoning that a non-zero exit would make `schedule:run` report failure every minute
> one bad number appeared. That reasoning contradicts FR-023 and was wrong. The exit code is now the
> operator's outage signal; the per-conversation isolation is what keeps a single failure from
> destroying the rest of the run's work. `contracts/cli-command.md` was corrected to match.

**Alternatives considered**:
- *Re-throwing to abort the run.* Rejected: violates per-conversation isolation for the remaining
  thousands of conversations.
- *Isolating failures AND returning `SUCCESS` always.* Rejected: defeats the FR-023 outage signal.
- *Silent catch.* Rejected: FR-023 explicitly requires a recorded reason, which is an observability
  requirement, not just a debugging convenience.

---

## Proposed replacement constitution

`.specify/memory/constitution.md` governs nothing in this repository. Below is a draft for approval.
**This has not been written to disk** — replacing a governance artifact is the user's call.

Principles carried across from the current document, re-scoped to this project:

| Proposed principle | Carried from |
|---|---|
| I. Conversation state is a closed, enumerated set. State transitions live in the routers, never inline. | IV (data model as source of truth) |
| II. Data model is the source of truth for conversation state; per-conversation scratch data belongs in `data`. | IV |
| III. Simplicity and provisional scope: the smallest design that satisfies the spec, no speculative abstraction. | V |
| IV. Surface ambiguity, do not auto-resolve it: an undefined behaviour is a clarification, not a default. | III |
| V. Scheduled work must be idempotent, re-runnable, and must not overlap. | new (learned from FR-024) |
| VI. Outbound third-party sends must not be recorded as successful until confirmed. | new (learned from FR-011) |

Explicitly **not** carried over: Docker Compose service decomposition, FastAPI, PostgreSQL/pgvector,
Redis task queues, HubSpot, and Anthropic. None are present here.

## Unresolved items

| Item | Impact | Status |
|---|---|---|
| Constitution replacement not approved | Blocks `/speckit.tasks` | Awaiting user decision |
| Active git branch is still `staging`; no `002-whatsapp-idle-nudge` branch exists | No impact on artifacts | Ask before branching |
| No schema migration needed | Confirmed | Resolved (D-005, D-008) |
| WHATSAPP_GRAPH_VERSION `v23.0` is hard-coded in `SendMessage.php` | Pre-existing, outside this feature's scope | Noted only |
# Implementation Plan: WhatsApp Idle Conversation Nudge

**Branch**: `002-whatsapp-idle-nudge` (not yet created — see note) | **Date**: 2026-10-05 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/002-whatsapp-idle-nudge/spec.md`

## Summary

Add a scheduled check that finds WhatsApp conversations abandoned mid-flow, messages the user
asking whether they wish to end the chat, and offers three buttons: resume the interrupted flow,
or return to the menu that matches their audience (patients get the patient menu, doctors and
assistants get the staff menu).

The interrupted flow is identified by a single `active()` definition held on the conversation-state
enum. Prompting moves the conversation into a new, dedicated prompt state that is deliberately
excluded from `active()`, which is what prevents a user who ignores the prompt from being prompted
again. Resume pops the conversation's stored pending-step sequence, falling back to the state the
conversation was in when it was interrupted.

## Technical Context

**Language/Version**: PHP 8.3+ (constraint `^8.3`; local CLI runs 8.5.9)

**Primary Dependencies**: Laravel Framework 12.62, Eloquent ORM, WhatsApp Cloud API Graph v23.0
(reached through the existing `SendMessage` HTTP client). No new Composer dependencies.

**Storage**: MySQL 8 (primary, per `.env` `DB_CONNECTION=mysql`); SQLite `:memory:` for tests.
No schema migration required — the feature reuses the existing
`whats_app_conversations.last_activity_at` and `.data` columns.

**Testing**: PHPUnit 11.5 via `php artisan test`, using `RefreshDatabase` + `Http::fake()`

**Target Platform**: Linux server running `php artisan schedule:run` every minute; recipients are
WhatsApp users reached through the Meta Cloud API

**Project Type**: Web service (Laravel API) with a scheduled console command

**Performance Goals**: A run that finds up to 10,000 qualifying conversations completes inside one
60-second interval (SC-006), which implies at least ~170 conversations/second end-to-end including
an outbound API call each

**Constraints**:
- WhatsApp interactive messages support a maximum of 3 reply buttons — the design uses exactly 3
- Outbound sends are rate-limited by the Cloud API; a batch of 10k prompts will be throttled
- Runs must not overlap (FR-024)
- The prompt must not be persisted as "sent" unless the API call actually succeeded (FR-011),
  because the prompt state is excluded from `active()` and would never be retried

**Scale/Scope**: 1 enum method + 1 enum case, 1 new state handler class, 1 deleted handler class,
2 router arms changed, 1 new console command, 1 schedule entry, 1 new feature test file. No
migration, no new config, no API route changes.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

**Gate result: BLOCKED.** Not because this feature conflicts with a project principle, but because
`.specify/memory/constitution.md` documents a different system and does not govern this repository.
This must be resolved before `/speckit.tasks`.

The constitution in `.specify/memory/constitution.md` is titled *"Sales Inbound Workflow
Constitution"* and its stated principles are:

| # | Principle as written | Applies here? | Assessment |
|---|---|---|---|
| I | Independently deployable services; communicate only over HTTP in Docker Compose; no cross-service DB access | No | This is a single monolithic Laravel application. There are no services to deploy independently. |
| II | API-first, FastAPI by default; every service exposes `/health` | No | The project is PHP/Laravel. FastAPI is not installed and no Python service exists. |
| III | Human-in-the-loop for ambiguity; never silently auto-resolve | Informal alignment | The spec does refuse to guess: undecided behaviours were surfaced as clarifications rather than assumed. Worth carrying into the replacement constitution. |
| IV | Data model is the source of truth for structured state | Yes, and satisfied | Conversation state, pending-step sequence and activity markers live in the database. |
| V | Simplicity and provisional scope; build the smallest version | Yes, and satisfied | One enum method, one command, no schema change, no speculative abstraction. |
| — | Technology constraints: Docker Compose, FastAPI, PostgreSQL + pgvector, Redis task queue, HubSpot CRM, Anthropic | No | None of these exist in this repository. Enforcing them would forbid every implementation option available here. |

**Why this is a block rather than a justified violation.** The gate permits a violation that is
*justified* — a conscious, argued departure from a principle that genuinely applies. That defence
is unavailable here: FastAPI, Docker Compose, pgvector, Redis and HubSpot are not constraints this
project has chosen to bend, they are constraints written for a codebase that is not this one. The
document's own Governance section states it "supersedes ad hoc technical decisions", so taken
literally it forbids implementing this feature at all.

**Resolution.** Replace `.specify/memory/constitution.md` with principles matching this repository
before implementation. A replacement drafted for approval is included in
[research.md](./research.md) § "Proposed replacement constitution". Principles worth carrying over
from the current document: IV (data model as source of truth), V (simplicity and provisional scope),
and III's spirit (do not silently auto-resolve ambiguity — surface it).

### Post-design re-check (after Phase 1)

Re-evaluated against the finished design. **Result: unchanged — still BLOCKED**, and the gate is not
weakened by the design work.

- Design introduced **no** Docker Compose service, no FastAPI/Python component, no PostgreSQL/pgvector
  usage, no Redis queue, no HubSpot integration and no Anthropic dependency. It adds one Artisan
  command, one enum case, one state handler and one schedule entry to an existing Laravel app
  (plan.md § Project Structure).
- The two principles that genuinely apply (data model as source of truth; simplicity and provisional
  scope) are **satisfied**, not merely tolerated: the design adds no schema migration and reuses
  `state`, `data`, `last_activity_at` and the existing pending-step sequence, with no speculative
  abstraction introduced.
- The block therefore cannot be cleared by design changes. It can only be cleared by ratifying a
  replacement constitution or explicitly waiving the document for this repository.

## Project Structure

### Documentation (this feature)

```text
specs/002-whatsapp-idle-nudge/
├── plan.md              # This file (/speckit.plan command output)
├── spec.md              # Feature specification
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── checklists/
│   └── requirements.md  # Spec quality checklist
├── contracts/           # Phase 1 output
└── tasks.md             # Phase 2 output (/speckit.tasks — NOT created here)
```

### Source Code (repository root)

```text
app/
├── Enums/
│   └── ConversationState.php              # MODIFIED: + IDLE_CHECK case, + active() method, - IDLE case
├── APIServices/WhatsApp/
│   ├── ConversationRouter.php             # MODIFIED: IDLE arm -> IDLE_CHECK, drop IdleState import
│   ├── ExecutionRouter.php                # MODIFIED: IDLE arm -> IDLE_CHECK, drop IdleState import
│   ├── SendMessage.php                    # UNCHANGED: buttons() already supports the 3-button payload
│   ├── ConversationManager.php            # UNCHANGED: already stamps last_activity_at per inbound msg
│   └── States/
│       ├── IdleState.php                  # DELETED (FR-025)
│       ├── IdleCheckState.php             # NEW: execute() + handleResponse()
│       ├── Start.php                      # MODIFIED: only the stale comment on line 12
│       └── MainMenu.php / AdminMenu.php   # UNCHANGED: reused for the "return to menu" outcome
├── Console/Commands/
│   └── CheckIdleConversations.php         # NEW: app:check-idle-conversations
└── Models/
    └── WhatsAppConversation.php           # UNCHANGED: existing columns/casts suffice

routes/
└── console.php                            # MODIFIED: + ->everyMinute() schedule

tests/
└── Feature/
    └── IdleConversationNudgeTest.php      # NEW
```

**Structure Decision**: Follows the repository's existing conventions rather than introducing a new
layout. Console commands live in `app/Console/Commands` with a `protected $signature` and
`handle(): int` returning `self::SUCCESS`/`self::FAILURE`; scheduling is declared in
`routes/console.php` (there is no `app/Console/Kernel.php` — this is the Laravel 11+ skeleton);
WhatsApp behaviour lives in `app/APIServices/WhatsApp/States` as static `execute()`/`handleResponse()`
pairs reached through the two parallel routers. Tests live flat in `tests/Feature`.

No migration is created. `whats_app_conversations` already has `state` (string, cast to the enum),
`step`, `data` (JSON, cast to array) and `last_activity_at` (cast to datetime), which is everything
FR-002, FR-009, FR-013 and FR-015 need.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Constitution describes a different project | Cannot be justified as a bend of an applicable rule; see Constitution Check | Proceeding without ratifying a replacement leaves the SDD workflow governed by constraints for an unrelated codebase |

## Notes

- **Branch**: `.specify/extensions.yml` does not exist, so no `before_plan` hook ran and no git
  branch was created. `setup-plan.sh` reports `002-whatsapp-idle-nudge` because it derives the
  value from `.specify/feature.json`; the actual checked-out branch is `staging`.
- **Reusing `Start::execute` for both menu buttons** avoids duplicating the patient-versus-staff
  branch that already exists at `app/APIServices/WhatsApp/States/Start.php:13-21`. It requires
  synthesizing a message array of the shape `['from' => $conversation->phone_number]`, because
  `Start::execute` and `MainMenu::execute` both read `$message['from']` and declare a non-nullable
  `array $message`, whereas a scheduled run has no inbound message. `AdminMenu::execute` already
  reads `$conversation->phone_number` instead and is cron-safe as written.
- **`IdleState::execute` currently declares `array $message` non-nullably** and is reached through
  `ExecutionRouter::execute($conversation, ?array $message = null)`. Any state handler invoked by
  the scheduled command must declare the parameter nullable or it will raise a `TypeError`.

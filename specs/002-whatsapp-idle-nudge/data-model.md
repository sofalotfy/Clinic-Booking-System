# Data Model: WhatsApp Idle Conversation Nudge

**Phase**: 1 (Design & Contracts) | **Date**: 2026-10-05
**Input**: [spec.md](./spec.md), [research.md](./research.md)

## Summary

**No schema migration is required.** Every field this feature reads or writes already exists. The
design adds one enum case, one enum method, and three keys inside the existing `data` JSON column.

| Concept (spec entity) | Backing store | Change |
|---|---|---|
| Conversation | `whats_app_conversations` row | none |
| Conversation step / Prompt state | `whats_app_conversations.state` | one new enum case |
| User profile | `users` row, joined via `user_id` | none |
| Pending-step sequence | `whats_app_conversations.data.callStack` | none |
| Last-active marker | `whats_app_conversations.last_activity_at` | none |

---

## Entity: Conversation (existing — `app/Models/WhatsAppConversation.php`)

Only the columns this feature touches are documented.

| Column | Type / cast | Role in this feature |
|---|---|---|
| `id` | bigint PK | Iteration cursor for `chunkById`; logged on failure |
| `state` | string, cast → `ConversationState` | Filtered by `whereIn(..., ConversationState::active())`; overwritten to `idle_check` on a successful prompt |
| `step` | string, nullable | Cleared by `Start::execute` on both the resume and menu paths so a multi-field form restarts from its first field (FR-016, FR-018, D-009) |
| `data` | JSON, cast → array | Holds `callStack` plus the two snapshot keys added by this feature |
| `last_activity_at` | datetime | The idle test: `<= now() - minutes` (FR-002) |
| `expires_at` | datetime | **Not read.** Redundant with `last_activity_at + 10 min`; see research D-005 |
| `phone_number` | string | Recipient number passed to `SendMessage::buttons` |
| `user_id` | FK → `users`, nullable | Source of the greeting name |
| `doctor_whatsapp_account_id` | FK → `doctor_whatsapp_accounts` | Source of `phone_number_id` and `access_token` |

**Relationships used**: `user()` (BelongsTo, greeting name), `doctorWhatsAppAccount()` (BelongsTo,
Cloud API credentials). Both are eager-loaded in the sweep to avoid N+1 across up to 10,000 rows
(research D-005).

## Entity: ConversationState (existing enum — `app/Enums/ConversationState.php`)

```text
ADD     case IDLE_CHECK = 'idle_check';
REMOVE  case IDLE = 'idle';
ADD     public static function active(): array
```

`active()` returns every case except the routing states, computed from `self::cases()` so that a
state added later is picked up automatically (research D-001):

```text
ROUTING_STATES = [START, MAIN_MENU, ADMIN_MENU, IDLE_CHECK]

active() = self::cases() filtered to exclude ROUTING_STATES
```

Excluded because they are entry points or menus rather than productive work (spec clarification Q1):
a conversation sitting at a menu is not stuck in anything.

### State transitions added by this feature

| From | Trigger | To | Guard |
|---|---|---|---|
| any state in `active()` | scheduled sweep, idle ≥ window, **send succeeded** | `IDLE_CHECK` | snapshot written alongside (D-007) |
| any state in `active()` | scheduled sweep, send failed | *unchanged* | retried next run |
| `IDLE_CHECK` | `continue_conversation` | popped `callStack` entry, else `idle_previous_state` | snapshot keys cleared |
| `IDLE_CHECK` | `end_conversation` / `back_to_mainmenu` | `MAIN_MENU` or `ADMIN_MENU` per profile | `callStack`, `step`, snapshot cleared |
| `IDLE_CHECK` | free-text reply | `IDLE_CHECK` | prompt re-sent, state untouched |

`IDLE_CHECK` is excluded from `active()`, which is precisely what implements FR-012: an ignored
prompt is never re-sent, and no separate "already prompted" flag exists to drift out of sync.

## Entity: `data` JSON keys (extended)

```text
callStack              array   existing — ordered pending steps; popped on resume
idle_previous_state    string  NEW — ConversationState value at prompt time; resume fallback (FR-014)
idle_prompted_at       string  NEW — ISO timestamp; operational breadcrumb, also the
                              already-prompted audit record required by FR-012
```

**Invariants**:

- `idle_previous_state` and `idle_prompted_at` are written together or not at all — a half-written
  snapshot would make FR-014 fall back to a state the conversation was never in.
- Both keys are cleared on **every** prompt button, and `callStack`/`step` are cleared on the menu
  path. Nothing from the snapshot outlives the interaction (D-009).
- Existing keys (`callStack` and whatever flow-specific keys are already in `data`) are preserved;
  only these named keys are touched.

## Entity: User profile (existing — `users`)

| Column | Role |
|---|---|
| `name` | string, **nullable**, the only name column in the schema — used for `اهلا {name}` |

When `name` is null or blank the greeting line is omitted and only the question is sent (research
D-013). No fallback to the phone number.

Profile-to-audience mapping is read from the conversation's account (`doctor_whatsapp_accounts`) and
resolved by the existing logic in `Start.php:13-21`; this feature adds no new audience field and no
role lookup (research D-010).

## Entity: DoctorWhatsAppAccount (existing — credentials, read-only)

| Column | Role |
|---|---|
| `phone_number_id` | Cloud API sender id |
| `access_token` | encrypted-cast bearer token for the send |
| `is_active` | **Not checked by this feature** — noted as a possible follow-up, not in scope |

## Validation rules

No new user input is validated, because the feature accepts none: the only inputs are the sweep's
elapsed-time comparison and three hard-coded button payloads the system itself defined. The
validation that does exist is the selector: a conversation qualifies only when **both**
`state ∈ active()` **and** `last_activity_at <= now() - minutes` (FR-002). Both clauses are required;
either alone over-prompts — state alone would prompt conversations the user just used, and time alone
would prompt menus and previously-asked conversations.
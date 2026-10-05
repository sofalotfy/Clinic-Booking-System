# Contract: WhatsApp Prompt and Reply Payloads

**Kind**: outbound message payload (to Meta Cloud API) + inbound button contract (from the existing
webhook)
**Provided by**: `app/APIServices/WhatsApp/SendMessage.php` (existing) and
`app/APIServices/WhatsApp/States/IdleCheckState.php` (new)
**Consumed by**: WhatsApp users through the clinic's messaging account

No new HTTP endpoint is introduced. Inbound replies arrive on the project's existing webhook route and
are dispatched by the existing `ConversationRouter`; this feature only adds one `match` arm.

---

## 1. Outbound: the idle prompt

Sent via the existing `SendMessage::buttons($phoneNumberId, $accessToken, $to, $text, $buttons)`, which
already builds a Cloud API `type: "interactive"` / `interactive.type: "button"` payload. **No change
to `SendMessage.php`.**

### Body text

```text
اهلا {{name}}
هل ترغب بإنهاء المحادثة ؟
```

| Line | Condition |
|---|---|
| `اهلا {{name}}` | included when `$conversation->user?->name` is non-null and non-blank |
| `هل ترغب بإنهاء المحادثة ؟` | always included |

When no name is available the greeting line is omitted and the body is the question alone (research
D-013). `users.name` is nullable and is the only name column in the schema, so this case is reachable.

Text follows the existing greeting style in this chat system: no hamza on the initial alef (`اهلا`,
not `أهلا`).

### Buttons

Exactly three. This is a hard platform limit — the Cloud API accepts a maximum of 3 reply buttons —
and the feature uses all three.

| Order | Label (displayed) | Payload `reply_id` | Effect |
|---|---|---|---|
| 1 | `إنهاء المحادثة` | `end_conversation` | Return to the menu matching the user's audience |
| 2 | `استكمال المحادثة` | `continue_conversation` | Pop `data.callStack` and resume the interrupted flow |
| 3 | `العودة للقائمة الرئيسية` | `back_to_mainmenu` | Return to the menu matching the user's audience |

Buttons 1 and 3 are **behaviourally identical** — both land on the same audience menu. They are kept
separate because the user specified both labels; they are not aliases with different behaviour, and
no code path may treat one differently from the other.

Payload ids are lowercase `snake_case`, consistent with the existing state's reply ids. They are
matched by exact string equality in `IdleCheckState::handleResponse`.

### Delivery ordering (load-bearing)

The prompt is sent **before** the conversation is written to `idle_check`. This is a contract on the
order of operations, not a stylistic choice:

1. `SendMessage::buttons(...)` returns successfully → write `state = idle_check` + snapshot keys
2. Send throws or returns unsuccessful → leave the row untouched; count it as failed

Because `idle_check` is excluded from `active()`, a conversation in that state is never re-examined.
Writing the state first would mean a transient network failure permanently strands a user who is
still mid-booking, with nothing left to retry (research D-007, FR-011, SC-003).

---

## 2. Inbound: replies to the prompt

Reached through `ConversationRouter` when `state === idle_check` and the inbound payload is a button
reply. The `ConversationRouter` arm dispatches to `IdleCheckState::handleResponse($conversation, $message)`.

| Inbound `reply_id` | Resulting state | Side effects |
|---|---|---|
| `continue_conversation` | popped `callStack` entry; else the snapshotted `idle_previous_state` | `callStack` popped; `step` cleared; both snapshot keys cleared; `last_activity_at` refreshed |
| `end_conversation` | `MAIN_MENU` or `ADMIN_MENU` per profile | `callStack`, `step` and both snapshot keys cleared; menu rendered |
| `back_to_mainmenu` | identical to `end_conversation` | identical to `end_conversation` |
| anything else (free text, unknown id) | *unchanged* (`idle_check`) | prompt re-sent; nothing else mutated |

### `handleResponse` contract

```text
handleResponse(WhatsAppConversation $conversation, array $message): WhatsAppConversation
```

Returns the updated, persisted conversation, matching the existing handler convention. `$message` is
**non-nullable** here because this path only ever runs from a real inbound reply.

The sibling `execute(WhatsAppConversation $conversation, ?array $message = null)` is
**nullable**, because `ExecutionRouter::execute()` is also the entry point used by the scheduled
command, which has no inbound message. The pre-existing `IdleState::execute` typed this parameter as
non-nullable, which would raise a `TypeError` on every scheduled run (research D-011).

### Resume fallback

`continue_conversation` resolves the target state in order:

1. If `data.callStack` is non-empty, pop its last entry and use it.
2. Otherwise use `data.idle_previous_state`, the state snapshotted when the prompt was sent.

The fallback is required, not defensive: `data.callStack` is empty more often than not in this
codebase — the existing pop in `InfoConfirmation.php` already tolerates an empty stack.

### Audience menu mapping

| Profile | Menu returned |
|---|---|
| Patient | `MAIN_MENU` |
| Doctor, Assistant | `ADMIN_MENU` |

Resolved by the existing branch in `Start::execute` (`app/APIServices/WhatsApp/States/Start.php:13-21`),
reused rather than reimplemented, so there is one source of truth for audience → menu (research D-010).

---

## 3. Compatibility notes

- **Removing `ConversationState::IDLE` is a breaking change to both routers' `match` arms.** Both
  `ConversationRouter.php:33` and `ExecutionRouter.php:33` must be updated in the same change that
  deletes the enum case, or static analysis fails on an unhandled enum value.
- **Webhook route**: unchanged. No new route, no change to inbound signature verification.
- **`SendMessage::buttons()`**: unchanged. Its 5-argument signature already fits this feature exactly,
  including the 3-button cap.
- **Conversation state values** are persisted as strings; the new value is the literal
  `idle_check`. Any analytics, admin listing, or manual support tooling that enumerates known state
  values by hand will need that value added.
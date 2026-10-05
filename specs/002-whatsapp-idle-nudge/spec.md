# Feature Specification: WhatsApp Idle Conversation Nudge

**Feature Branch**: `staging`

**Created**: 2026-10-05

**Status**: Draft

**Input**: User description: "i want to add a cron job to check the the left idle for to long chats and sends a message asking for what to do with the convesation it will only fire with conversation within certain states we define thoos states in the enum as a method called active that would return an array of the specified states and then we make the cron job that runs every minuite to check if any conversation that has one of those states and sends a message saying اهلا فلان / هل ترغب بإنهاء المحادثة ؟ with the name ofcourse and then give three buttons" + "the buttons are إنهاء المحادثة / استكمال المحادثة / العودة للقائمة الرئيسية first and last do the same thing send to the main menu but for the middle one u will have to use the call stack added to the data to pop the last state before going into this questioning state to pickup from there and there is alos a complexity in the part with admin menu since it's for doctors and assistants and the main menu is only for patients" + "make it have a new state witch is not included in active"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Resume an interrupted booking (Priority: P1)

A patient starts booking an appointment over WhatsApp, gives their name, then puts their phone down without finishing. Once they have been idle long enough, they receive a short Arabic greeting that names them, the question "هل ترغب بإنهاء المحادثة ؟", and three buttons. They tap "استكمال المحادثة" and their booking picks up again instead of starting over.

**Why this priority**: Abandoned bookings are the direct revenue loss this feature exists to recover. Every other story is a variation on this detect-prompt-resume loop.

**Independent Test**: Park one conversation mid-booking with a stale activity marker, run the idle check, tap the continue button, then confirm the conversation is back in a booking step and receives no further prompts.

**Acceptance Scenarios**:

1. **Given** a patient conversation sitting in an unfinished booking step whose last activity is older than the idle window, **When** the idle check runs, **Then** the patient receives exactly one message that greets them by name, asks "هل ترغب بإنهاء المحادثة ؟", and offers exactly three choices.
2. **Given** a patient who taps "استكمل المحادثة", **When** the reply is processed, **Then** the conversation resumes the flow it was interrupted in rather than starting a different or unrelated flow.
3. **Given** a conversation interrupted while part-way through a multi-field form, **When** the user resumes, **Then** the conversation returns to the flow recorded before the interruption, including when no resume point had been stored in the conversation's pending-step sequence, and that flow restarts from its first field rather than the field the user stopped on.

---

### User Story 2 - Land on the menu that matches the user (Priority: P2)

Both "إنهاء المحادثة" and "العودة للقائمة الرئيسية" return the user to their home menu. Patients land on the patient-facing menu. Doctors and assistants land on the staff menu, never the patient one.

**Why this priority**: Two audiences share one chat system. Sending clinic staff to a patient-facing menu is the most likely way to get this feature wrong, and it is invisible to anyone testing only as a patient.

**Independent Test**: Park one idle conversation per audience (a patient, a doctor, an assistant), run the check, tap the end button on each, and confirm patients reach the patient menu while staff reach the staff menu.

**Acceptance Scenarios**:

1. **Given** an interrupted conversation belonging to a patient, **When** the user taps either "إنهاء المحادثة" or "العودة للقائمة الرئيسية", **Then** the patient is shown the patient-facing menu and any partially-entered flow data is discarded.
2. **Given** an interrupted conversation belonging to a doctor or an assistant, **When** the user taps either of those two buttons, **Then** the user is shown the staff menu and is never shown the patient-facing menu.

---

### User Story 3 - Interrupt only productive flows, and only once (Priority: P3)

The check fires only for conversations parked in a meaningful in-progress step. It skips conversations already sitting at a menu, at the very start of a chat, or already showing the prompt. A conversation that ignores the prompt is not prompted again.

**Why this priority**: Without this the feature becomes spam, and repeated unsolicited messages get a clinic's number blocked.

**Independent Test**: Park conversations across every state, run the check, confirm only the in-progress subset is prompted, then run the check again and confirm nothing further is sent.

**Acceptance Scenarios**:

1. **Given** conversations sitting in a variety of states, **When** the idle check runs, **Then** only those whose state appears on the defined in-progress list are prompted.
2. **Given** conversations sitting at a menu, at the start of a chat, or already showing the prompt, **When** the idle check runs, **Then** those conversations are left untouched and receive nothing.
3. **Given** a conversation that was prompted and never replied, **When** the idle check runs again after another full idle window, **Then** no second prompt is sent to that conversation.
4. **Given** a user who resumed their flow, **When** the idle check runs again, **Then** the user is not immediately re-prompted, and the full idle window applies anew starting from the moment they resumed.

---

### Edge Cases

- **A user replies with free text instead of tapping a button** while the prompt is showing, after the prompt's buttons have stopped responding. The prompt is sent again, so repeated free-text replies repeat the prompt until the user chooses a button, resumes, or returns to a menu.
- **The prompt cannot be delivered** (messaging provider unreachable or rejecting the request). The conversation must not be left in the prompt state, because it is excluded from the in-progress list and would never be prompted again.
- **The user has no name on record**, so the greeting cannot include one.
- **The resume point popped from the pending-step sequence is itself an entry point or a menu** rather than an in-progress flow — for example a stored sequence whose only entry is the chat start.
- **A doctor or assistant is prompted**, so the greeting and the eventual menu must reflect the staff audience rather than the patient one.
- **Two executions of the check overlap** because a run takes longer than its interval.
- **A conversation's owning clinic messaging account has been removed** between being selected and being messaged.
- **The pending-step sequence is missing entirely** from a conversation's stored data.
- **A very large number of conversations qualify at once**, so the run must not be held in memory all at once.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST evaluate conversations for idleness automatically at least once every minute.
- **FR-002**: A conversation qualifies for a prompt only when both conditions hold: its current step appears on the defined in-progress list, **and** its last recorded activity is older than the idle window.
- **FR-003**: The idle window MUST default to 10 minutes, matching the existing conversation expiry behaviour.
- **FR-004**: The system MUST expose the in-progress list as a single named definition held alongside the conversation states, so that introducing a new state is a one-place change rather than a change to the checking logic.
- **FR-005**: The in-progress list MUST exclude: the start of a chat, the patient menu, the staff menu, and the new prompt state.
- **FR-006**: The prompt MUST be delivered as a single message containing a greeting that names the user, followed by the question "هل ترغب بإنهاء المحادثة ؟".
- **FR-007**: The greeting MUST use the user's recorded name; when no name is recorded the greeting MUST omit the name rather than send a blank or placeholder.
- **FR-008**: The prompt MUST offer exactly three choices: "إنهاء المحادثة", "استكمال المحادثة", and "العودة للقائمة الرئيسية".
- **FR-009**: The system MUST record a distinct prompt state as the conversation's current step when prompting, so a prompted conversation is distinguishable from one in a normal in-progress flow.
- **FR-010**: The prompt state MUST NOT appear on the in-progress list, so that a conversation already showing the prompt is never prompted a second time.
- **FR-011**: A conversation MUST enter the prompt state only when the prompt was successfully delivered. A failed delivery MUST leave the conversation unchanged so that a later run retries it.
- **FR-012**: A delivery failure for one conversation MUST NOT prevent any other conversation from being processed.
- **FR-013**: Choosing "استكمال المحادثة" MUST restore the flow taken from the top of the conversation's stored pending-step sequence, which was recorded as the step to return to before entering the prompt state.
- **FR-014**: When that stored pending-step sequence is empty or absent, the system MUST fall back to the step the conversation was in when it was interrupted.
- **FR-015**: Resuming MUST also record the user as active at that moment, so the full idle window applies again from the resume rather than from the original interruption.
- **FR-016**: Resuming MUST restore the flow identified by FR-013 or FR-014 but MUST NOT restore progress made within that flow, so a multi-field form restarts from its first field and values previously entered into the interrupted form are discarded.
- **FR-017**: Choosing either "إنهاء المحادثة" or "العودة للقائمة الرئيسية" MUST return the user to the menu that matches their audience, and both choices MUST behave identically.
- **FR-018**: The user's audience MUST be determined from the user's profile type rather than from the conversation's current step, so a doctor or assistant is always recognised as staff.
- **FR-019**: Returning a user to a menu MUST discard any partially-entered flow data held for that conversation.
- **FR-020**: When a user replies with free text rather than one of the three choices while the prompt is showing, the system MUST send the prompt again, leaving the conversation in the prompt state and performing neither a resume nor a return to a menu.
- **FR-021**: The check MUST process qualifying conversations in bounded batches rather than loading every match into memory at once.
- **FR-022**: Each run MUST report how many conversations were prompted, skipped, and failed.
- **FR-023**: A run MUST report overall failure when at least one conversation failed, so that an operator or monitoring system can detect partial outages.
- **FR-024**: The system MUST NOT allow two executions of the check to overlap, so that a slow run cannot stack up behind the next interval.
- **FR-025**: The system MUST remove the superseded idle state and its handler as part of this work, so that exactly one state represents having asked the user whether to end the conversation.

### Key Entities *(include if feature involves data)*

- **Conversation**: An ongoing chat between one person and one clinic's messaging account. Holds the current step, the sequence of steps pending return, and the marker of when the person was last active.
- **Conversation step**: A named position within the chat flow. Some steps represent genuine in-progress work and are worth interrupting; others are menus or entry points that are not.
- **Prompt state**: A distinct step representing "the person was asked whether to end the chat and has not yet answered". Excluded from the in-progress list.
- **User profile**: A person reachable through the clinic's messaging account, recorded as a patient, a doctor, or an assistant. Determines which menu the person returns to and whether the staff menu applies.
- **Pending-step sequence**: An ordered list of steps held on the conversation, from which the step to resume is taken.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of conversations that become idle in an in-progress step receive the prompt within one minute of the idle window elapsing.
- **SC-002**: No conversation receives a second prompt without the user having replied and then been interrupted again.
- **SC-003**: A delivery failure never leaves a conversation waiting on a prompt the user never received.
- **SC-004**: 100% of patients returning via either menu choice reach the patient-facing menu, and 100% of doctors and assistants reach the staff menu.
- **SC-005**: Resuming returns the user to the same flow they were interrupted in, in 100% of cases.
- **SC-006**: A run that finds up to 10,000 qualifying conversations completes within a single one-minute interval.
- **SC-007**: An operator can determine prompted, skipped, and failed counts from the run's own output without inspecting logs.
- **SC-008**: When a resume restarts a multi-field form, no single interruption causes the same field to be requested from the user more than once.

## Assumptions

- The idle window of 10 minutes mirrors the conversation expiry already applied to every incoming message, so a conversation that has not been touched for that long is already treated as lapsed elsewhere.
- The "call stack" referenced for resuming is the ordered sequence of pending steps already stored on each conversation.
- The greeting wording follows the existing greeting style in the chat system, which does not use a hamza on the initial alef.
- Names are read from the user record; people who message the clinic for the first time are provisioned with a profile automatically before any conversation exists.
- The audiences served are patients, doctors, and assistants. Doctors and assistants share one staff menu rather than having separate menus.
- One global idle window applies to all clinics. Per-clinic or per-conversation windows are not introduced here.
- A person who ignores the prompt is simply left alone; the conversation is not deleted, closed, or archived.
- Out of scope: cleaning up conversations that have already expired, changing how any menu is constructed, adding a settings screen for the idle window, and re-notifying users who ignored a prompt.
- The idle window is overridable per run for testing purposes without being configurable in normal operation.
- Running the check more often than once a minute is harmless and does not need to be prevented.

## Clarifications

### Session 2026-10-05

- Q: Which states count as in-progress and therefore eligible for a prompt? → A: The complement of the four routing states (chat start, patient menu, staff menu, and the idle state that this feature later removes), with the new prompt state additionally excluded.
- Q: What should resuming restore? → A: The top of the conversation's stored pending-step sequence, falling back to the step recorded before the interruption when that sequence is empty.
- Q: Should the prompt state be a new state or reuse the existing idle state? → A: A new dedicated state, deliberately excluded from the in-progress list so an ignored prompt is never repeated.
- Q: How should a user who does not want to continue be returned to a menu? → A: Both "إنهاء المحادثة" and "العودة للقائمة الرئيسية" send the user to their home menu, chosen by profile type so staff reach the staff menu rather than the patient menu.
- Q: When resuming a conversation interrupted part-way through a multi-field form, is the partially entered field preserved? → A: No. Resuming restores the flow but restarts it from its first field, discarding values already entered into that form.
- Q: What happens if a user replies with free text instead of tapping a button while the prompt is showing? → A: The prompt is sent again; the conversation stays in the prompt state and neither resumes nor returns to a menu.
- Q: Should the superseded idle state and its handler be removed? → A: Yes, remove both, so exactly one state represents having asked the user whether to end the conversation.

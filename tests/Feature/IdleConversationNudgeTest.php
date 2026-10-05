<?php

namespace Tests\Feature;

use App\APIServices\WhatsApp\ConversationRouter;
use App\APIServices\WhatsApp\States\IdleCheckState;
use App\Enums\ConversationState;
use App\Models\Doctor;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Patient;
use App\Models\User;
use App\Models\WhatsAppConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Command\Command;
use Tests\TestCase;

class IdleConversationNudgeTest extends TestCase
{
    use RefreshDatabase;

    private const GRAPH = 'graph.facebook.com/*';

    /**
     * Registered per test rather than in setUp: Http::fake() keeps the first
     * stub registered for a URL pattern, so a later fake cannot override it.
     */
    private function fakeGraph(int $status = 200): void
    {
        Http::fake([
            self::GRAPH => Http::response(
                $status === 200 ? ['success' => true] : ['error' => 'rejected'],
                $status
            ),
        ]);
    }

    // ---------------------------------------------------------------------
    // Fixtures
    // ---------------------------------------------------------------------

    private function account(): DoctorWhatsAppAccount
    {
        $unique = uniqid();

        $doctorUser = User::create([
            'name' => 'Doctor '.$unique,
            'phone' => '111'.$unique,
            'email' => "doctor+{$unique}@example.com",
            'password' => bcrypt('password'),
        ]);

        return DoctorWhatsAppAccount::create([
            'doctor_id' => Doctor::create(['user_id' => $doctorUser->id])->id,
            'phone_number_id' => 'pnid-'.$unique,
            'access_token' => 'token-'.$unique,
            'is_active' => true,
        ]);
    }

    private function patientUser(string $name = 'John Doe'): User
    {
        $unique = uniqid();

        $user = User::create([
            'name' => $name,
            'phone' => '222'.$unique,
            'email' => "patient+{$unique}@example.com",
            'password' => bcrypt('password'),
        ]);

        Patient::create(['user_id' => $user->id]);

        return $user;
    }

    private function staffUser(): User
    {
        $unique = uniqid();

        $user = User::create([
            'name' => 'Staff '.$unique,
            'phone' => '333'.$unique,
            'email' => "staff+{$unique}@example.com",
            'password' => bcrypt('password'),
        ]);

        Doctor::create(['user_id' => $user->id]);

        return $user;
    }

    private function conversation(
        User $user,
        ConversationState $state,
        array $data = [],
        ?DoctorWhatsAppAccount $account = null,
        ?string $lastActivityAt = null
    ): WhatsAppConversation {
        return WhatsAppConversation::create([
            'doctor_whatsapp_account_id' => ($account ?? $this->account())->id,
            'user_id' => $user->id,
            'phone_number' => '20123456789',
            'state' => $state,
            'step' => 'confirm_phone',
            'data' => $data,
            'last_activity_at' => $lastActivityAt ?? now()->subMinutes(30),
            'expires_at' => now()->subMinutes(20),
        ]);
    }

    /**
     * A conversation parked in the prompt state, as if a sweep had prompted it.
     * Used instead of running the sweep so each story is testable in isolation.
     */
    private function prompted(
        User $user,
        array $data = [],
        ?DoctorWhatsAppAccount $account = null
    ): WhatsAppConversation {
        return $this->conversation(
            $user,
            ConversationState::IDLE_CHECK,
            array_merge([
                IdleCheckState::KEY_PREVIOUS_STATE => ConversationState::BOOK_SLOT->value,
                IdleCheckState::KEY_PROMPTED_AT => now()->subMinute()->toDateTimeString(),
            ], $data),
            $account,
            now()->subMinutes(11)
        );
    }

    private function reply(string $id): array
    {
        return ['type' => 'interactive', 'value' => $id, 'from' => '20123456789'];
    }

    /**
     * Runs the sweep and returns [exitCode, output]. Read the output directly
     * rather than via expectsOutputToContain(), which does not reliably match
     * against the full command output in this suite.
     *
     * @return array{0: int, 1: string}
     */
    private function sweep(int $minutes = 10): array
    {
        $code = Artisan::call('app:check-idle-conversations', ['--minutes' => $minutes]);

        return [$code, trim(Artisan::output())];
    }

    // ---------------------------------------------------------------------
    // US1 - Resume an interrupted booking
    // ---------------------------------------------------------------------

    public function test_it_sends_the_prompt_with_three_buttons_and_the_user_name()
    {
        $this->fakeGraph();
        $conversation = $this->conversation(
            $this->patientUser('Sara Ahmed'),
            ConversationState::BOOK_SLOT
        );

        IdleCheckState::execute($conversation);

        Http::assertSentCount(1);

        $request = Http::recorded()[0][0];
        $payload = $request->data();

        $this->assertStringContainsString('Sara Ahmed', $payload['interactive']['body']['text']);
        $this->assertStringContainsString(
            'هل ترغب بإنهاء المحادثة ؟',
            $payload['interactive']['body']['text']
        );
        $this->assertCount(3, $payload['interactive']['action']['buttons']);

        $titles = array_column($payload['interactive']['action']['buttons'], 'reply');
        $this->assertSame('إنهاء المحادثة', $titles[0]['title']);
        $this->assertSame('استكمال المحادثة', $titles[1]['title']);
        $this->assertSame('العودة للقائمة الرئيسية', $titles[2]['title']);
    }

    public function test_resuming_pops_the_call_stack()
    {
        $this->fakeGraph();
        $conversation = $this->prompted($this->patientUser(), [
            'callStack' => [ConversationState::CONFIRM_BOOKING->value, ConversationState::BOOK_SLOT->value],
        ]);

        IdleCheckState::handleResponse($conversation, $this->reply(IdleCheckState::CONTINUE_CONVERSATION));

        $conversation->refresh();

        $this->assertSame(ConversationState::CONFIRM_BOOKING, $conversation->state);
        $this->assertSame([ConversationState::BOOK_SLOT->value], $conversation->data['callStack']);
    }

    public function test_resuming_falls_back_to_the_previous_state_when_the_stack_is_empty()
    {
        $this->fakeGraph();
        $conversation = $this->prompted($this->patientUser());

        IdleCheckState::handleResponse($conversation, $this->reply(IdleCheckState::CONTINUE_CONVERSATION));

        $this->assertSame(ConversationState::BOOK_SLOT, $conversation->fresh()->state);
    }

    public function test_resuming_clears_the_step_so_a_form_restarts_at_its_first_field()
    {
        $this->fakeGraph();
        $conversation = $this->prompted($this->patientUser());

        $this->assertSame('confirm_phone', $conversation->step);

        IdleCheckState::handleResponse($conversation, $this->reply(IdleCheckState::CONTINUE_CONVERSATION));

        $conversation->refresh();

        $this->assertNull($conversation->step);
        $this->assertArrayNotHasKey(IdleCheckState::KEY_PREVIOUS_STATE, $conversation->data);
        $this->assertArrayNotHasKey(IdleCheckState::KEY_PROMPTED_AT, $conversation->data);
    }

    public function test_resuming_refreshes_activity_so_no_second_prompt_fires()
    {
        $this->fakeGraph();
        $conversation = $this->prompted($this->patientUser());

        $this->assertTrue($conversation->last_activity_at->isBefore(now()->subMinutes(5)));

        IdleCheckState::handleResponse($conversation, $this->reply(IdleCheckState::CONTINUE_CONVERSATION));

        $this->assertTrue($conversation->fresh()->last_activity_at->greaterThan(now()->subMinutes(5)));
    }

    // ---------------------------------------------------------------------
    // US2 - Land on the menu that matches the user
    // ---------------------------------------------------------------------

    public function test_ending_the_conversation_sends_a_patient_to_the_patient_menu()
    {
        $this->fakeGraph();
        $conversation = $this->prompted($this->patientUser());

        IdleCheckState::handleResponse($conversation, $this->reply(IdleCheckState::END_CONVERSATION));

        $this->assertSame(ConversationState::MAIN_MENU, $conversation->fresh()->state);
    }

    public function test_ending_the_conversation_sends_staff_to_the_staff_menu()
    {
        $this->fakeGraph();
        $conversation = $this->prompted($this->staffUser());

        IdleCheckState::handleResponse($conversation, $this->reply(IdleCheckState::END_CONVERSATION));

        $this->assertSame(ConversationState::ADMIN_MENU, $conversation->fresh()->state);
    }

    public function test_back_to_main_menu_behaves_identically_to_ending_the_conversation()
    {
        $this->fakeGraph();
        $ending = $this->prompted($this->patientUser());
        $back = $this->prompted($this->patientUser());

        IdleCheckState::handleResponse($ending, $this->reply(IdleCheckState::END_CONVERSATION));
        IdleCheckState::handleResponse($back, $this->reply(IdleCheckState::BACK_TO_MAIN_MENU));

        $this->assertSame($ending->fresh()->state, $back->fresh()->state);
    }

    public function test_menu_choices_discard_partial_flow_data()
    {
        $this->fakeGraph();
        $conversation = $this->prompted($this->patientUser(), [
            'callStack' => [ConversationState::BOOK_SLOT->value],
            'half_entered_phone' => '0100',
        ]);

        IdleCheckState::handleResponse($conversation, $this->reply(IdleCheckState::END_CONVERSATION));

        $fresh = $conversation->fresh();

        $this->assertNull($fresh->step);
        $this->assertArrayNotHasKey('callStack', $fresh->data);
        $this->assertArrayNotHasKey('half_entered_phone', $fresh->data);
    }

    // ---------------------------------------------------------------------
    // US3 - Interrupt only productive flows, and only once
    // ---------------------------------------------------------------------

    public function test_active_states_exclude_every_routing_state()
    {
        $active = ConversationState::active();

        foreach (ConversationState::ROUTING_STATES as $routing) {
            $this->assertNotContains($routing, $active, "{$routing->value} must not be active");
        }

        $this->assertContains(ConversationState::BOOK_SLOT, $active);
        $this->assertContains(ConversationState::BOOK_APPOINTMENT, $active);
    }

    public function test_the_sweep_prompts_only_when_both_conditions_hold()
    {
        $this->fakeGraph();
        $user = $this->patientUser();

        $shouldPrompt = $this->conversation($user, ConversationState::BOOK_SLOT);
        $freshButActive = $this->conversation(
            $user,
            ConversationState::BOOK_SLOT,
            [],
            null,
            now()
        );
        $atMenuButOld = $this->conversation($user, ConversationState::MAIN_MENU);

        $this->artisan('app:check-idle-conversations', ['--minutes' => 10])->assertSuccessful();

        Http::assertSentCount(1);

        $this->assertSame(
            ConversationState::IDLE_CHECK,
            $shouldPrompt->fresh()->state
        );
        $this->assertNotSame(
            ConversationState::IDLE_CHECK,
            $freshButActive->fresh()->state
        );
        $this->assertNotSame(
            ConversationState::IDLE_CHECK,
            $atMenuButOld->fresh()->state
        );
    }

    public function test_a_conversation_already_prompted_is_never_prompted_again()
    {
        $this->fakeGraph();
        $this->prompted($this->patientUser());

        $this->artisan('app:check-idle-conversations', ['--minutes' => 10])->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_a_failed_send_leaves_the_conversation_eligible_for_the_next_run()
    {
        $this->fakeGraph(500);
        $conversation = $this->conversation(
            $this->patientUser(),
            ConversationState::BOOK_SLOT
        );

        $this->artisan('app:check-idle-conversations', ['--minutes' => 10])
            ->assertFailed();

        $this->assertSame(ConversationState::BOOK_SLOT, $conversation->fresh()->state);
    }

    public function test_the_run_reports_prompted_skipped_and_failed_counts()
    {
        $this->fakeGraph();
        $this->conversation($this->patientUser(), ConversationState::BOOK_SLOT);

        [$code, $output] = $this->sweep();

        $this->assertStringContainsString('1 prompted', $output);
        $this->assertStringContainsString('0 skipped', $output);
        $this->assertStringContainsString('0 failed', $output);
        $this->assertSame(Command::SUCCESS, $code);
    }

    public function test_free_text_while_prompted_sends_the_prompt_again_and_keeps_the_state()
    {
        $this->fakeGraph();
        $conversation = $this->prompted($this->patientUser());

        IdleCheckState::handleResponse($conversation, [
            'type' => 'text',
            'value' => 'ما هو هذا',
            'from' => '20123456789',
        ]);

        // The prompt is re-sent ...
        Http::assertSentCount(1);
        $this->assertStringContainsString(
            'هل ترغب بإنهاء المحادثة ؟',
            Http::recorded()[0][0]->data()['interactive']['body']['text']
        );

        // ... and the conversation neither resumed nor returned to a menu.
        $fresh = $conversation->fresh();
        $this->assertSame(ConversationState::IDLE_CHECK, $fresh->state);
        $this->assertSame(
            ConversationState::BOOK_SLOT->value,
            $fresh->data[IdleCheckState::KEY_PREVIOUS_STATE],
            'A re-prompt must not overwrite the resume snapshot.'
        );
    }

    public function test_a_blank_name_omits_the_greeting_but_keeps_the_question()
    {
        $this->fakeGraph();
        $conversation = $this->conversation(
            $this->patientUser('   '),
            ConversationState::BOOK_SLOT
        );

        IdleCheckState::execute($conversation);

        $body = Http::recorded()[0][0]->data()['interactive']['body']['text'];

        $this->assertSame('هل ترغب بإنهاء المحادثة ؟', $body);
        $this->assertStringNotContainsString('اهلا', $body);
    }

    public function test_the_conversation_router_dispatches_prompt_replies()
    {
        $this->fakeGraph();
        $conversation = $this->prompted($this->patientUser());

        ConversationRouter::execute(
            $conversation,
            $this->reply(IdleCheckState::CONTINUE_CONVERSATION)
        );

        $this->assertSame(ConversationState::BOOK_SLOT, $conversation->fresh()->state);
    }

    public function test_one_failure_does_not_abort_the_rest_of_the_batch()
    {
        // Deterministic per-conversation failure: keyed on the sender id rather than
        // on call order, so the test does not depend on sequence timing.
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'rejecting-sender')) {
                return Http::response(['error' => 'rejected'], 500);
            }

            return Http::response(['success' => true], 200);
        });

        $user = $this->patientUser();

        $rejecting = $this->account();
        $rejecting->update(['phone_number_id' => 'rejecting-sender']);

        $first = $this->conversation($user, ConversationState::BOOK_SLOT, [], $rejecting);
        $second = $this->conversation($user, ConversationState::BOOK_SLOT);

        [$code, $output] = $this->sweep();

        $this->assertStringContainsString('1 prompted', $output);
        $this->assertStringContainsString('1 failed', $output);
        $this->assertSame(Command::FAILURE, $code);

        $this->assertSame(ConversationState::BOOK_SLOT, $first->fresh()->state);
        $this->assertSame(ConversationState::IDLE_CHECK, $second->fresh()->state);
    }
}

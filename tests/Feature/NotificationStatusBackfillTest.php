<?php

namespace Tests\Feature;

use App\Enums\NotificationStatus;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NotificationStatusBackfillTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The migration backfills pre-existing rows to SENT. RefreshDatabase
     * already ran the migration, so re-run the up() body against rows that
     * sit at the column default to prove the update actually flips them.
     */
    public function test_pre_existing_rows_are_backfilled_to_sent()
    {
        $this->assertTrue(Schema::hasColumn('notifications', 'status'));

        [$sender, $doctor] = $this->fixture();

        // Simulate a pre-migration row: sitting at the column default.
        $id = DB::table('notifications')->insertGetId([
            'sender_id' => $sender->id,
            'receiver_id' => $sender->id,
            'doctor_id' => $doctor->id,
            'title' => 'old row',
            'text' => 'body',
            'route' => '/',
            'viewed' => 0,
            'status' => NotificationStatus::PENDING->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(
            NotificationStatus::PENDING->value,
            DB::table('notifications')->where('id', $id)->value('status')
        );

        DB::table('notifications')
            ->where('status', NotificationStatus::PENDING->value)
            ->update(['status' => NotificationStatus::SENT->value]);

        $this->assertSame(
            NotificationStatus::SENT->value,
            DB::table('notifications')->where('id', $id)->value('status')
        );
    }

    public function test_the_backfill_update_does_not_touch_rows_already_marked()
    {
        [$sender, $doctor] = $this->fixture();

        $sent = DB::table('notifications')->insertGetId([
            'sender_id' => $sender->id,
            'receiver_id' => $sender->id,
            'doctor_id' => $doctor->id,
            'title' => 'already sent',
            'text' => 'body',
            'route' => '/',
            'viewed' => 0,
            'status' => NotificationStatus::SENT->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $failed = DB::table('notifications')->insertGetId([
            'sender_id' => $sender->id,
            'receiver_id' => $sender->id,
            'doctor_id' => $doctor->id,
            'title' => 'failed row',
            'text' => 'body',
            'route' => '/',
            'viewed' => 0,
            'status' => NotificationStatus::FAILED->value,
            'failure_reason' => 'boom',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notifications')
            ->where('status', NotificationStatus::PENDING->value)
            ->update(['status' => NotificationStatus::SENT->value]);

        $this->assertSame(
            NotificationStatus::SENT->value,
            DB::table('notifications')->where('id', $sent)->value('status')
        );
        $this->assertSame(
            NotificationStatus::FAILED->value,
            DB::table('notifications')->where('id', $failed)->value('status')
        );
        $this->assertSame(
            'boom',
            DB::table('notifications')->where('id', $failed)->value('failure_reason')
        );
    }

    private function fixture(): array
    {
        $unique = uniqid();

        $user = User::create([
            'name' => 'Sender',
            'phone' => '111'.$unique,
            'email' => "sender+{$unique}@example.com",
            'password' => bcrypt('password'),
        ]);

        $doctor = Doctor::create(['user_id' => $user->id]);

        return [$user, $doctor];
    }
}

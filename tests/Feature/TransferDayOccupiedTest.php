<?php

namespace Tests\Feature;

use App\APIServices\Days\TransferDay as TransferDayApi;
use App\Enums\DayStatus;
use App\Models\Day;
use App\Models\Doctor;
use App\Models\User;
use App\Services\DaysInstances\Checks\CheckDayOccupied;
use App\Services\DaysInstances\Modifications\TransferDay as TransferDayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransferDayOccupiedTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_reports_occupied_when_an_active_day_exists_for_the_doctor()
    {
        [$user, $doctor] = $this->doctor();
        $this->day($doctor, '2026-11-01');

        $this->assertTrue(CheckDayOccupied::execute($doctor->id, '2026-11-01'));
    }

    public function test_check_reports_free_for_a_different_date()
    {
        [$user, $doctor] = $this->doctor();
        $this->day($doctor, '2026-11-01');

        $this->assertFalse(CheckDayOccupied::execute($doctor->id, '2026-11-02'));
    }

    public function test_check_ignores_cancelled_days()
    {
        [$user, $doctor] = $this->doctor();
        $this->day($doctor, '2026-11-01', DayStatus::CANCELLED);

        $this->assertFalse(CheckDayOccupied::execute($doctor->id, '2026-11-01'));
    }

    public function test_check_is_scoped_to_the_doctor()
    {
        [$user, $doctor] = $this->doctor();
        [$otherUser, $otherDoctor] = $this->doctor('other@example.com', '222');
        $this->day($otherDoctor, '2026-11-01');

        $this->assertFalse(CheckDayOccupied::execute($doctor->id, '2026-11-01'));
    }

    public function test_api_service_rejects_a_transfer_to_an_occupied_date()
    {
        [$user, $doctor] = $this->doctor();
        $source = $this->day($doctor, '2026-11-01');
        $this->day($doctor, '2026-11-02');

        $this->expectException(ValidationException::class);

        TransferDayApi::execute($this->transferRequest($user, $source, '2026-11-02'), $source);
    }

    public function test_api_service_rejection_targets_the_new_date_field()
    {
        [$user, $doctor] = $this->doctor();
        $source = $this->day($doctor, '2026-11-01');
        $this->day($doctor, '2026-11-02');

        try {
            TransferDayApi::execute($this->transferRequest($user, $source, '2026-11-02'), $source);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('new_date', $exception->errors());
        }
    }

    public function test_service_rejects_a_transfer_to_an_occupied_date()
    {
        [$user, $doctor] = $this->doctor();
        $source = $this->day($doctor, '2026-11-01');
        $this->day($doctor, '2026-11-02');

        $this->expectException(ValidationException::class);

        TransferDayService::execute($user, $source, '2026-11-02');
    }

    private function transferRequest(User $user, Day $source, string $newDate): Request
    {
        $request = Request::create("/days/{$source->id}/transfer", 'POST', [
            'new_date' => $newDate,
        ]);

        $request->setUserResolver(fn () => $user);

        return $request;
    }

    private function doctor(string $email = 'doctor@example.com', string $phone = '111'): array
    {
        $user = User::create([
            'name' => 'Doctor',
            'email' => $email,
            'phone' => $phone,
            'password' => bcrypt('password'),
        ]);

        return [$user, Doctor::create(['user_id' => $user->id])];
    }

    private function day(Doctor $doctor, string $date, DayStatus $status = DayStatus::ACTIVE): Day
    {
        return Day::create([
            'doctor_id' => $doctor->id,
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'appointment_duration' => 30,
            'queue_length' => 0,
            'status' => $status,
        ]);
    }
}

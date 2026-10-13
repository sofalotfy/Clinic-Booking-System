<?php

namespace Tests\Feature;

use App\Enums\DayStatus;
use App\Enums\NotificationEnum;
use App\Enums\UserType;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Day;
use App\Models\Doctor;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Patient;
use App\Models\User;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DoctorDayAppointmentsNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            'graph.facebook.com/*' => Http::response(['success' => true], 200),
        ]);
    }

    public function test_it_notifies_the_clinic_two_hours_before_the_schedule_starts()
    {
        Carbon::setTestNow(Carbon::today()->setTime(8, 0, 0));
        $fixture = $this->fixture();

        $this->artisan('app:notify-doctor-day-appointments')
            ->assertExitCode(0);

        Http::assertSent(function ($request) use ($fixture) {
            return str_contains($request->url(), 'graph.facebook.com')
                && ($request['to'] ?? null) === $fixture['clinic']->notifications_phone
                && ($request['template']['name'] ?? null) === NotificationEnum::DOCTOR_DAY_APPOINTMENTS->templateName()
                && $this->bodyParameters($request) === [
                    ['name' => 'name', 'value' => $fixture['doctorUser']->name],
                    ['name' => 'confirmed', 'value' => ArabicDateFormatter::toArabicDigits(2)],
                    ['name' => 'available', 'value' => ArabicDateFormatter::toArabicDigits(2)],
                    ['name' => 'time', 'value' => ArabicDateFormatter::formatTime('10:00')],
                ];
        });
    }

    public function test_it_ignores_schedules_outside_the_two_hour_window()
    {
        Carbon::setTestNow(Carbon::today()->setTime(7, 0, 0));
        $this->fixture();

        $this->artisan('app:notify-doctor-day-appointments')
            ->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_it_reports_when_no_schedule_matches()
    {
        Carbon::setTestNow(Carbon::today()->setTime(8, 0, 0));

        $this->artisan('app:notify-doctor-day-appointments')
            ->expectsOutputToContain('No schedules starting')
            ->assertExitCode(0);
    }

    /**
     * Named body parameters, keyed by parameter_name, so a placeholder rename
     * in the handler shows up here rather than as a rejected WhatsApp send.
     */
    private function bodyParameters($request): array
    {
        return collect($request['template']['components'] ?? [])
            ->filter(fn ($component) => ($component['type'] ?? null) === 'body')
            ->flatMap(fn ($component) => $component['parameters'] ?? [])
            ->map(fn ($parameter) => [
                'name' => $parameter['parameter_name'] ?? null,
                'value' => $parameter['text'] ?? null,
            ])
            ->values()
            ->all();
    }

    private function fixture(): array
    {
        $unique = uniqid();

        $doctorUser = User::create([
            'name' => 'Doctor Name',
            'phone' => '111'.$unique,
            'email' => "doctor+{$unique}@example.com",
            'type' => UserType::DOCTOR,
            'password' => bcrypt('password'),
        ]);

        $doctor = Doctor::create(['user_id' => $doctorUser->id]);

        $clinic = Clinic::create([
            'doctor_id' => $doctor->id,
            'notifications_phone' => '999'.$unique,
        ]);

        DoctorWhatsAppAccount::create([
            'doctor_id' => $doctor->id,
            'phone_number_id' => '123456789',
            'access_token' => 'dummy_token',
            'is_active' => true,
        ]);

        $patientUser = User::create([
            'name' => 'John Doe',
            'phone' => '222'.$unique,
            'email' => "patient+{$unique}@example.com",
            'type' => UserType::PATIENT,
            'password' => bcrypt('password'),
        ]);

        $patient = Patient::create(['user_id' => $patientUser->id]);

        $day = Day::create([
            'doctor_id' => $doctor->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'appointment_duration' => 30,
            'queue_length' => 0,
            'status' => DayStatus::ACTIVE,
        ]);

        foreach (['10:00', '10:30'] as $time) {
            Appointment::create([
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'date' => Carbon::today()->setTimeFromTimeString($time)->toDateTimeString(),
                'duration' => 30,
            ]);
        }

        return compact('doctorUser', 'doctor', 'clinic', 'patientUser', 'patient', 'day');
    }
}

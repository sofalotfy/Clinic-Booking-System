<?php

namespace App\APIServices\Appointments;

use App\Enums\UserType;
use App\Models\Patient;
use App\Models\User;
use App\Services\Appointments\Checks\CheckBookAppointment;
use App\Services\Appointments\Creation\BookAppointment as BookService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BookAppointment
{
    public static function execute(Request $request)
    {
        // VALIDATE
        $validated = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:20'],
            'name' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'min:1', 'max:120'],
            'area' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
        ])->validate();

        // NOTE: if the client sends only a date (no time), this resolves to 00:00.
        $dateTime = Carbon::parse($validated['date']);

        // FETCH USER BY PHONE (NO CREATION YET)
        $user = User::where('phone', $validated['phone'])->first();

        // EXISTING USER MUST BE A PATIENT (checked first so the error is accurate)
        if ($user && ! $user->isPatient()) {
            throw ValidationException::withMessages([
                'phone' => 'This phone number is not registered as a patient.',
            ]);
        }

        // NEW PATIENTS MUST HAVE A NAME
        if (! $user && empty($validated['name'])) {
            throw ValidationException::withMessages([
                'name' => 'Name is required for new patients.',
            ]);
        }

        // FAST-FAIL CHECK (NO SIDE EFFECTS). The service re-checks under a lock.
        $verdict = CheckBookAppointment::execute(
            $request->user(),
            $user?->patient,
            $dateTime,
            // pass a status here if this endpoint ever books non-ACTIVE appointments
        );

        if (! $verdict['valid']) {
            throw ValidationException::withMessages([
                'error' => $verdict['message'],
            ]);
        }

        // ACCOUNT CREATION + BOOKING IN ONE TRANSACTION:
        // if booking fails, the new user/patient are rolled back too.
        return DB::transaction(function () use ($request, $validated, $dateTime, $user, $verdict) {
            $user ??= User::firstOrCreate(
                ['phone' => $validated['phone']],
                [
                    'name' => $validated['name'],
                    'age' => $validated['age'] ?? null,
                    'area' => $validated['area'] ?? null,
                    'type' => UserType::PATIENT,
                ],
            );

            // Covers legacy users that are patients but have no Patient row.
            $patient = Patient::firstOrCreate(['user_id' => $user->id]);

            return BookService::execute(
                $request->user(),
                $patient,
                $verdict['day'],
                $dateTime->format('H:i'),
            );
        });
    }
}

<?php

namespace App\APIServices\Patients;

use App\Models\Patient;
use App\Services\Patients\Retrievals\ShowPatient as ShowService;
use Barryvdh\DomPDF\Facade\Pdf;

class PatientPdf
{
    public static function execute($request, Patient $patient)
    {
        $doctor = $request->user()->clinicDoctor();

        $patient = ShowService::execute($request->user(), $patient)
            ->select(ShowPatient::getSelects())
            ->get()
            ->load([
                'appointments' => function ($query) use ($doctor) {
                    $query
                        ->where('doctor_id', $doctor->id)
                        ->orderByDesc('date');
                },

                'flags' => function ($query) use ($doctor) {
                    $query->wherePivot('doctor_id', $doctor->id);
                },

                'notes' => function ($query) use ($doctor) {
                    $query
                        ->where('notes.doctor_id', $doctor->id)
                        ->orderByDesc('created_at');
                },

                'blocks' => function ($query) use ($doctor) {
                    $query
                        ->where('doctor_id', $doctor->id)
                        ->active();
                },
            ])
            ->first();

        abort_unless($patient, 404);

        $patient->load('user');

        $avatar = null;

        if ($patient->avatar && file_exists(public_path('storage/' . $patient->avatar))) {
            $avatar = 'data:image/jpeg;base64,' . base64_encode(
                file_get_contents(public_path('storage/' . $patient->avatar))
            );
        }

        $doctor->loadMissing(['user', 'clinic']);

        return Pdf::loadView('patients.pdf', [
            'patient' => $patient,
            'doctor'  => $doctor,
            'avatar'  => $avatar,
        ])->stream("patient-{$patient->id}.pdf");
    }
}

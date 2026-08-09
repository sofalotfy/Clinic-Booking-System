<?php

namespace App\APIServices\WhatsApp\AI\Services;

use App\APIServices\Appointments\SmartBookAppointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Enums\AppointmentStatus;
use Illuminate\Support\Facades\Log;
use Throwable;

class BookAppointmentTool
{
    /**
     * Define the schema so the AI model knows when and how to call this tool.
     */
    public static function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'book_appointment',
                'description' => 'Book or update an appointment for a patient with a doctor on a specific date and time.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'patient_id' => [
                            'type' => 'integer',
                            'description' => 'The integer ID of the patient.',
                        ],
                        'doctor_id' => [
                            'type' => 'integer',
                            'description' => 'The integer ID of the doctor.',
                        ],
                        'date' => [
                            'type' => 'string',
                            'description' => 'The full appointment datetime in YYYY-MM-DD HH:MM:SS format (e.g., "2026-08-10 14:30:00").',
                        ],
                        'duration' => [
                            'type' => 'integer',
                            'description' => 'Duration of the appointment in minutes. Default is 30.',
                        ],
                        'status' => [
                            'type' => 'string',
                            'description' => 'The status of the appointment (e.g., "active", "queued"). Default is "active".',
                        ],
                    ],
                    'required' => ['patient_id', 'doctor_id', 'date'],
                ],
            ],
        ];
    }

    /**
     * Execute the appointment booking/updating logic.
     */
    public static function handle(array $args): array
    {
        Log::info('BookAppointmentTool Called', ['args' => $args]);

        try {
            $patientId = $args['patient_id'] ?? null;
            $doctorId  = $args['doctor_id'] ?? null;
            $date      = $args['date'] ?? null;
            $duration  = $args['duration'] ?? 30; // Default 30 mins
            $statusRaw = $args['status'] ?? 'active';

            if (!$patientId || !$doctorId || !$date) {
                return [
                    'status' => 'error',
                    'message' => 'patient_id, doctor_id, and date are required parameters.',
                ];
            }

            // Resolve models
            $patient = Patient::find($patientId);
            $doctor  = Doctor::find($doctorId);

            if (!$patient) {
                return [
                    'status' => 'error',
                    'message' => "Patient with ID {$patientId} not found.",
                ];
            }

            if (!$doctor) {
                return [
                    'status' => 'error',
                    'message' => "Doctor with ID {$doctorId} not found.",
                ];
            }

            // Resolve Status Enum (adjust based on your enum values)
            $status = match (strtolower($statusRaw)) {
                'queued' => AppointmentStatus::QUEUED,
                default  => AppointmentStatus::ACTIVE,
            };

            // Execute service
            $appointment = SmartBookAppointment::execute($patient, $doctor, $date, $duration, $status);

            return [
                'status' => 'success',
                'message' => 'Appointment successfully booked/updated.',
                'appointment' => [
                    'id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'date' => $appointment->date,
                    'duration' => $appointment->duration,
                    'status' => $appointment->status,
                ],
            ];

        } catch (Throwable $e) {
            Log::error('BookAppointmentTool Error: ' . $e->getMessage(), [
                'exception' => $e,
                'args' => $args,
            ]);

            return [
                'status' => 'error',
                'message' => 'Failed to book appointment: ' . $e->getMessage(),
            ];
        }
    }
}
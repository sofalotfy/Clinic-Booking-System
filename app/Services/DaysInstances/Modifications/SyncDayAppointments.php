<?php

namespace App\Services\DaysInstances\Modifications;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentUpdateNotificationTypes;
use App\Models\Appointment;
use App\Services\Appointments\Modifications\QueueAppointment;
use App\Services\Appointments\Modifications\ResheduleAppointment;
use Carbon\Carbon;

class SyncDayAppointments
{
    /*
    PARAMETERS:
        1. Day Model Instance
            assumed that the day schedule is already updated but the appointments need to sync with it


    ALGORITHM:
        1. FETCH APPOINTMENTS
        2. GENERATE SLOTS
        3. ITERATE
            - FIND CLOSEST SLOT
            - RESCHEDULE APPOINTMENT
            - MARK THE SLOT AS USED
            - IF NO SLOT AVAILABLE
                - QUEUE APPOINTMENT


    NOTES:
        1.There might be an optimization if we use a minimum loss algorithm
            taking into prespective the appointments array as a whole
            not just doing this greedy algorithm with one appointment scope
    -

    */

    public static function execute($user, $day): void
    {
        // FETCH APPOINTMENTS
        $appointments = self::AppointmentsToRescheduleFinder($day);

        // GENERATE SLOTS FOR THE NEW DAY SCHEDULE
        $slotPool = new SlotPool(SlotGenerator::generate($day));

        foreach ($appointments as $appointment) {
            self::rescheduleAppointment($user, $appointment, $slotPool, $day);
        }
    }

    private static function rescheduleAppointment($user, $appointment, SlotPool $slotPool, $day): void
    {
        // FIND CLOSET SLOT
        $appointmentTime = Carbon::parse($appointment->date);
        $slotIndex = $slotPool->findClosestAvailable($appointmentTime);

        if ($slotIndex === null) {
            // NO SLOT AVAILABLE
            QueueAppointment::execute(
                $user,
                $appointment,
                $day->appointment_duration
            );

            return;
        }

        // MARK THE SLOT AS USED
        $slotPool->markUsed($slotIndex);

        // RESCHEDULE THE APPOINTMENT
        ResheduleAppointment::execute(
            $user,
            $appointment,
            $slotPool->slotAt($slotIndex)->toDateTimeString(),
            $day->appointment_duration,
            AppointmentUpdateNotificationTypes::COLIDE
        );
    }

    private static function AppointmentsToRescheduleFinder($day)
    {
        return Appointment::whereDate('date', $day->date)
            ->where('doctor_id', $day->doctor_id)
            ->whereIn('status', AppointmentStatus::working())
            ->orderBy('date')
            ->get();
    }
}

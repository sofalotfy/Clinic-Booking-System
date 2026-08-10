<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TemplatePlans\CreteaTemplate;
use App\APIServices\Days\GetAvailableSlots;
use App\APIServices\Doctors\GetAvailableDays;
USE App\Services\TemplatePlans\ActivatePlan;
use App\Services\TemplatePlans\CountColidingAppoinments;
use App\APIServices\Days\MapAppointments;
use App\APIServices\Days\GetDayAppointments;
use App\Models\Day;
use App\Services\Appointments\BulkUpdateAppointment;
use App\Services\Notifications\Doctor\PatientRename;
use App\Models\Patient;
use App\Models\Doctor;
use App\Services\Assistants\GetAssistantsContacts;

class TestController extends Controller
{
    public function test(Request $request)
    {
        GetAssistantsContacts::execute(Doctor::find(1));
    }

    public function makePlan(Request $request)
    {
        $plan = CreteaTemplate::execute($request->doctor_id, $request->days);
        return response()->json([
            'success' => true,
            'message' => 'Plan created successfully!',
        ]);
    }
}

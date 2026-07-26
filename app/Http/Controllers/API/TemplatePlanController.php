<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TemplatePlans\CreteaTemplate;
use App\Services\TemplatePlans\UpdatePlan;
use App\Services\TemplatePlans\DeletePlan;
use App\Models\TemplatePlan;
use App\Enums\TemplatePlanStatus;
use App\Services\TemplatePlans\ActivatePlan;
use App\Services\TemplatePlans\CountColidingAppoinments;
use App\Services\TemplatePlans\CountTruncatedAppointments;
use App\Services\TemplatePlans\CountOverflowedAppointments;
use Carbon\Carbon;

class TemplatePlanController extends Controller
{
    public function store(Request $request)
    {
        $plan = CreteaTemplate::execute($request->name, $request->description, $request->days);
        return response()->json([
            'plan' => $plan,
        ]);
    }

    public function index()
    {
        $plans = TemplatePlan::with('templateDays')->where('doctor_id', auth()->user()->doctor->id)->get();
        return response()->json([
            'plans' => $plans,
        ]);
    }

    public function show($id)
    {
        $plan = TemplatePlan::with('templateDays')->where('doctor_id', auth()->user()->doctor->id)->find($id);
        return response()->json([
            'plan' => $plan,
        ]);
    }

    public function update($id, Request $request)
    {
        $plan = UpdatePlan::execute(
            $request->input('name', null),
            $request->input('description', null),
            $request->input('days', null),
            $id
        );

        return response()->json([
            'plan' => $plan,
        ]);
    }

    public function destroy($id)
    {
        $plan = DeletePlan::execute($id);

        return response()->json([
            'message' => 'Plan deleted successfully',
        ]);
    }

    public function check(Request $request, $id)
    {
        $template = TemplatePlan::with('templateDays')
            ->where('doctor_id', auth()->user()->doctor->id)
            ->findOrFail($id);

        $start_date = $request->input('start_date', Carbon::now()->addDays(1)->toDateString());

        $colidingAppointments = count(CountColidingAppoinments::execute($template, $start_date));
        $truncatedAppointments = count(CountTruncatedAppointments::execute($template, $start_date));
        $overflowedAppointments = count(CountOverflowedAppointments::execute($template, $start_date));

        return response()->json([
            'colliding_appointments' => $colidingAppointments,
            'truncated_appointments' => $truncatedAppointments,
            'overflowed_appointments' => $overflowedAppointments,
        ]);
    }

    public function activate($id,Request $request)
    {
        $plan = ActivatePlan::execute($id,$request->start_date);

        return response()->json([
            'message' => 'Plan activated successfully',
        ]);
    }
}

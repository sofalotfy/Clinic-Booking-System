<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TemplatePlans\CreteaTemplate;
use App\Services\TemplatePlans\UpdatePlan;
use App\Models\TemplatePlan;
use App\Enums\TemplatePlanStatus;


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

    public function delete($id)
    {
        $plan = TemplatePlan::where('doctor_id', auth()->user()->doctor->id)->find($id);
        $plan->delete();
        return response()->json([
            'message' => 'Plan deleted successfully',
        ]);
    }

    public function activate($id)
    {
        $plan = TemplatePlan::where('doctor_id', auth()->user()->doctor->id)->find($id);
        $plan->update([
            'status' => TemplatePlanStatus::ACTIVE,
        ]);
        return response()->json([
            'message' => 'Plan activated successfully',
        ]);
    }

    public function deactivate($id)
    {
        $plan = TemplatePlan::where('doctor_id', auth()->user()->doctor->id)->find($id);
        $plan->update([
            'status' => TemplatePlanStatus::INACTIVE,
        ]);
        return response()->json([
            'message' => 'Plan deactivated successfully',
        ]);
    }
}

<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TemplatePlan;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Enums\AssistantPermissionsEnum;
use App\APIServices\TemplatePlans\ListPlans;
use App\APIServices\TemplatePlans\ShowPlan;
use App\APIServices\TemplatePlans\StorePlan;
use App\APIServices\TemplatePlans\UpdatePlan;
use App\APIServices\TemplatePlans\DeletePlan;
use App\APIServices\TemplatePlans\CheckRePlan;
use App\APIServices\TemplatePlans\ActivatePlan;

class TemplatePlanController extends Controller implements HasMiddleware
{ 
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_PLANS->value,
                only: ['index']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_SINGLE_PLAN->value . ',templatePlan',
                only: ['show']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::CREATE_PLAN->value,
                only: ['store']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_PLAN->value . ',templatePlan',
                only: ['update']    
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::DELETE_PLAN->value . ',templatePlan',
                only: ['destroy']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_PLAN->value . ',templatePlan',
                only: ['checkRePlan']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_PLAN->value . ',templatePlan',
                only: ['activate']
            ),
        ];
    }

    public function index(Request $request)
    {
        return ListPlans::execute($request);
    }

    public function show(Request $request, TemplatePlan $templatePlan)
    {
        return ShowPlan::execute($request, $templatePlan);
    }

    public function store(Request $request)
    {
        return StorePlan::execute($request);
    }

    public function update(Request $request, TemplatePlan $templatePlan)
    {
        return UpdatePlan::execute($request, $templatePlan);
    }

    public function destroy(Request $request, TemplatePlan $templatePlan)
    {
        return DeletePlan::execute($request, $templatePlan);
    }

    public function checkRePlan(Request $request, TemplatePlan $templatePlan)
    {
        return CheckRePlan::execute($request, $templatePlan);
    }

    public function activate(Request $request, TemplatePlan $templatePlan)
    {
        return ActivatePlan::execute($request, $templatePlan);
    }
}

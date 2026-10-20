<?php

namespace App\Http\Controllers\API;

use App\APIServices\Medicines\ListMedicines;
use App\APIServices\Medicines\ShowMedicine;
use App\Enums\AssistantPermissionsEnum;
use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MedicineController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_MEDICINES->value,
                only: ['index']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_SINGLE_MEDICINE->value,
                only: ['show']
            ),
        ];
    }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'medicines' => ListMedicines::execute($request),
        ]);
    }

    public function show(Medicine $medicine)
    {
        return response()->json([
            'success' => true,
            'medicine' => ShowMedicine::execute($medicine),
        ]);
    }
}

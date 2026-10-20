<?php

namespace App\Http\Controllers\Admin;

use App\AdminServices\Medicines\ImportMedicines;
use App\AdminServices\Medicines\ListMedicineImports;
use App\Enums\AdminPermissionsEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MedicineImportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin.permission:'.AdminPermissionsEnum::IMPORT_MEDICINES->value),
        ];
    }

    public function create()
    {
        return view('admin.medicines.import', [
            'imports' => ListMedicineImports::execute(),
            'chunkSize' => ImportMedicines::CHUNK_SIZE,
        ]);
    }

    public function chunk(Request $request)
    {
        ImportMedicines::storeChunk($request);

        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $import = ImportMedicines::execute($request);

        return redirect()->route('web-admin.medicines.import')
            ->with('status', "Sheet synced: {$import->created_count} added, {$import->updated_count} updated, "
                ."{$import->skipped_count} skipped, {$import->duplicate_count} duplicates ignored.");
    }
}

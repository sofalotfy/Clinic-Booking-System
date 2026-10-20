<?php

namespace App\AdminServices\Medicines;

use App\Models\MedicineImport;

class ListMedicineImports
{
    public static function execute()
    {
        return MedicineImport::query()
            ->with('uploader')
            ->latest()
            ->paginate(10)
            ->through(fn (MedicineImport $import) => [
                'id' => $import->id,
                'file_name' => $import->file_name,
                'uploaded_by' => $import->uploader?->name,
                'total_rows' => $import->total_rows,
                'created_count' => $import->created_count,
                'updated_count' => $import->updated_count,
                'skipped_count' => $import->skipped_count,
                'duplicate_count' => $import->duplicate_count,
                'errors' => $import->errors ?? [],
                'created_at' => $import->created_at?->format('Y-m-d H:i'),
            ]);
    }
}

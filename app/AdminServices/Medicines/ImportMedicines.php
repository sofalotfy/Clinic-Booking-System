<?php

namespace App\AdminServices\Medicines;

use App\Services\Medicines\ImportMedicines as ImportMedicinesService;

class ImportMedicines
{
    public static function execute($request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt|max:51200',
        ]);

        // A full drug price sheet is tens of thousands of rows
        set_time_limit(600);
        ini_set('memory_limit', '512M');

        $file = $request->file('file');

        return ImportMedicinesService::execute(
            $file->getRealPath(),
            $file->getClientOriginalName(),
            $request->user()
        );
    }
}

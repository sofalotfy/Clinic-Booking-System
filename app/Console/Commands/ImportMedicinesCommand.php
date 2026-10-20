<?php

namespace App\Console\Commands;

use App\Services\Medicines\ImportMedicines;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class ImportMedicinesCommand extends Command
{
    protected $signature = 'app:import-medicines {path : Path to the .xlsx or .csv drug price sheet}';

    protected $description = 'Sync the medicine catalog with a drug price sheet: update existing trade names, add new ones.';

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! is_file($path)) {
            $this->error("File not found [{$path}].");

            return self::FAILURE;
        }

        try {
            $import = ImportMedicines::execute($path, basename($path));
        } catch (ValidationException $exception) {
            $this->error(collect($exception->errors())->flatten()->implode(' '));

            return self::FAILURE;
        }

        $this->info("{$import->total_rows} rows: {$import->created_count} added, {$import->updated_count} updated, "
            ."{$import->skipped_count} skipped, {$import->duplicate_count} duplicates ignored.");

        foreach ($import->errors ?? [] as $error) {
            $this->line("  - {$error}");
        }

        return self::SUCCESS;
    }
}

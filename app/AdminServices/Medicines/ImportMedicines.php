<?php

namespace App\AdminServices\Medicines;

use App\Services\Medicines\ImportMedicines as ImportMedicinesService;
use App\Support\ChunkedUpload;
use Illuminate\Support\Str;

class ImportMedicines
{
    /**
     * 512KB keeps every request under the default nginx client_max_body_size
     * (1MB) and PHP upload_max_filesize (2MB). 200 pieces allows ~100MB sheets.
     */
    public const CHUNK_SIZE = 512 * 1024;

    private const MAX_CHUNKS = 200;

    public static function storeChunk($request): void
    {
        $validated = $request->validate([
            'upload_id' => 'required|uuid',
            'index' => 'required|integer|min:0|max:'.(self::MAX_CHUNKS - 1),
            'chunk' => 'required|file|max:'.(self::CHUNK_SIZE / 1024 + 64),
        ]);

        ChunkedUpload::storeChunk($validated['upload_id'], (int) $validated['index'], $request->file('chunk'));
    }

    public static function execute($request)
    {
        // A full drug price sheet is tens of thousands of rows
        set_time_limit(600);
        ini_set('memory_limit', '512M');

        // Without JavaScript the form posts the file in one piece
        if ($request->hasFile('file')) {
            $request->validate(['file' => 'required|file|mimes:xlsx,csv,txt|max:51200']);

            $file = $request->file('file');

            return ImportMedicinesService::execute($file->getRealPath(), $file->getClientOriginalName(), $request->user());
        }

        $validated = $request->validate([
            'upload_id' => 'required|uuid',
            'chunks' => 'required|integer|min:1|max:'.self::MAX_CHUNKS,
            'file_name' => ['required', 'string', 'max:255', 'regex:/\.(xlsx|csv)$/i'],
        ], [
            'upload_id.required' => 'Please choose a file to upload.',
            'file_name.regex' => 'Only .xlsx and .csv files are supported.',
        ]);

        ChunkedUpload::pruneStale();

        try {
            $path = ChunkedUpload::assemble(
                $validated['upload_id'],
                (int) $validated['chunks'],
                Str::lower(pathinfo($validated['file_name'], PATHINFO_EXTENSION))
            );

            return ImportMedicinesService::execute($path, $validated['file_name'], $request->user());
        } finally {
            ChunkedUpload::discard($validated['upload_id']);
        }
    }
}

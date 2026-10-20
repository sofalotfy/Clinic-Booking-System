<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

/**
 * Receives a file in small pieces and stitches it back together, so files
 * larger than the server's upload_max_filesize / client_max_body_size can
 * still be uploaded from the browser. Pieces live under a per-upload folder
 * named by a client generated UUID, validated before it touches the disk.
 */
class ChunkedUpload
{
    private const ROOT = 'app/chunked-uploads';

    private const STALE_AFTER_SECONDS = 86400;

    public static function storeChunk(string $uploadId, int $index, UploadedFile $chunk): void
    {
        $folder = self::folder($uploadId);

        File::ensureDirectoryExists($folder);

        $chunk->move($folder, (string) $index);
    }

    /**
     * Joins the pieces into one file and returns its path. The caller must
     * call discard() once done with it.
     */
    public static function assemble(string $uploadId, int $chunks, string $extension): string
    {
        $folder = self::folder($uploadId);
        $path = "{$folder}/assembled.{$extension}";

        $output = fopen($path, 'wb');

        try {
            for ($index = 0; $index < $chunks; $index++) {
                $piece = "{$folder}/{$index}";

                if (! is_file($piece)) {
                    throw ValidationException::withMessages(['file' => 'The upload was incomplete, please try again.']);
                }

                $input = fopen($piece, 'rb');
                stream_copy_to_stream($input, $output);
                fclose($input);
            }
        } finally {
            fclose($output);
        }

        return $path;
    }

    public static function discard(string $uploadId): void
    {
        File::deleteDirectory(self::folder($uploadId));
    }

    /**
     * Removes pieces left behind by uploads that were abandoned midway.
     */
    public static function pruneStale(): void
    {
        if (! is_dir(storage_path(self::ROOT))) {
            return;
        }

        foreach (File::directories(storage_path(self::ROOT)) as $folder) {
            if (filemtime($folder) < time() - self::STALE_AFTER_SECONDS) {
                File::deleteDirectory($folder);
            }
        }
    }

    private static function folder(string $uploadId): string
    {
        // The id names a folder on disk, so it must be exactly a UUID
        if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uploadId)) {
            throw ValidationException::withMessages(['file' => 'Invalid upload id.']);
        }

        return storage_path(self::ROOT.'/'.strtolower($uploadId));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChunkUploadController extends Controller
{
    private const CHUNK_DIR = 'chunked-uploads';

    private const MAX_CHUNK_AGE_HOURS = 24;

    /**
     * Receive a single chunk of a file.
     * JS sends: file, chunkIndex, totalChunks, uuid, fieldName, museumId (optional)
     */
    public function storeChunk(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'chunkIndex' => 'required|integer|min:0',
            'totalChunks' => 'required|integer|min:1',
            'uuid' => 'required|string|size:36',
            'fieldName' => 'required|string|in:path_obj,obj_file',
            'museum_id' => 'nullable|integer',
        ]);

        $uuid = $request->uuid;
        $chunkIndex = (int) $request->chunkIndex;
        $totalChunks = (int) $request->totalChunks;
        $fieldName = $request->fieldName;

        $chunkDir = $this->getChunkDir($uuid, $fieldName);
        $chunkPath = $chunkDir.'/chunk_'.str_pad($chunkIndex, 6, '0', STR_PAD_LEFT).'.tmp';

        $file = $request->file('file');
        $fileSize = $file->getSize();
        Storage::disk('local')->putFileAs($chunkDir, $file, basename($chunkPath));

        // Check if this is the last chunk
        if ($chunkIndex === $totalChunks - 1) {
            $expectedTotal = $this->getExpectedTotalChunks($uuid, $fieldName);
            if ($expectedTotal !== null && $expectedTotal !== $totalChunks) {
                $this->cleanup($uuid, $fieldName);

                return response()->json(['error' => 'Chunk count mismatch.'], 422);
            }
        }

        Log::info("Chunk {$chunkIndex}/{$totalChunks} received", [
            'uuid' => $uuid,
            'fieldName' => $fieldName,
            'size' => $fileSize,
        ]);

        return response()->json([
            'received' => true,
            'chunkIndex' => $chunkIndex,
            'totalChunks' => $totalChunks,
        ]);
    }

    /**
     * Finalize upload: merge chunks + validate GLB + move to final location.
     * JS calls this after all chunks are uploaded.
     */
    public function finalize(Request $request)
    {
        $request->validate([
            'uuid' => 'required|string|size:36',
            'fieldName' => 'required|string|in:path_obj,obj_file',
            'museum_id' => 'nullable|integer',
            'target_path' => 'nullable|string', // e.g. 'virtual-museum/models' or 'virtual-museum/objects/models'
            'original_filename' => 'nullable|string',
        ]);

        $uuid = $request->uuid;
        $fieldName = $request->fieldName;
        $targetPath = $request->input('target_path', 'virtual-museum/models');
        $originalFilename = $request->input('original_filename', 'model.glb');

        $chunkDir = $this->getChunkDir($uuid, $fieldName);
        $tempMergedPath = $chunkDir.'/merged_'.$uuid.'.tmp';

        $chunks = $this->getSortedChunks($uuid, $fieldName);

        if ($chunks->isEmpty()) {
            Log::warning('Chunked upload: no chunks found', [
                'uuid' => $uuid,
                'fieldName' => $fieldName,
                'chunkDir' => $chunkDir,
            ]);

            return response()->json(['error' => 'No chunks found.'], 404);
        }

        // Merge chunks using storage paths
        $tempMergedFullPath = Storage::disk('local')->path($tempMergedPath);
        $out = fopen($tempMergedFullPath, 'wb');
        if ($out === false) {
            return response()->json(['error' => 'Cannot create merged file.'], 500);
        }

        foreach ($chunks as $chunkPath) {
            $chunkData = Storage::disk('local')->get($chunkPath);
            fwrite($out, $chunkData);
        }
        fclose($out);

        // Validate GLB magic bytes: first 4 bytes must be "glTF" (0x46546C67)
        $handle = fopen($tempMergedFullPath, 'rb');
        $header = fread($handle, 4);
        fclose($handle);

        if ($header !== 'glTF') {
            Storage::disk('local')->delete($tempMergedPath);
            $this->cleanup($uuid, $fieldName);

            return response()->json(['error' => 'File is not a valid GLB.'], 422);
        }

        // Move to final public storage location
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION) ?: 'glb';
        $filename = Str::uuid()->toString().'.'.strtolower($extension);
        $finalPath = "{$targetPath}/{$filename}";

        Storage::disk('public')->put($finalPath, Storage::disk('local')->get($tempMergedPath));
        $publicUrl = "/storage/{$finalPath}";

        // Cleanup
        Storage::disk('local')->delete($tempMergedPath);
        $this->cleanup($uuid, $fieldName);

        Log::info('Chunked upload finalized', [
            'uuid' => $uuid,
            'fieldName' => $fieldName,
            'finalPath' => $finalPath,
        ]);

        return response()->json([
            'success' => true,
            'path' => $finalPath,
            'filename' => $filename,
            'url' => $publicUrl,
        ]);
    }

    /**
     * Check if all chunks for a given upload are present.
     */
    public function status(Request $request)
    {
        $request->validate([
            'uuid' => 'required|string|size:36',
            'fieldName' => 'required|string|in:path_obj,obj_file',
            'totalChunks' => 'required|integer|min:1',
        ]);

        $uuid = $request->uuid;
        $fieldName = $request->fieldName;
        $totalChunks = (int) $request->totalChunks;

        $chunks = $this->getSortedChunks($uuid, $fieldName);
        $receivedCount = $chunks->count();

        return response()->json([
            'uuid' => $uuid,
            'fieldName' => $fieldName,
            'receivedChunks' => $receivedCount,
            'totalChunks' => $totalChunks,
            'complete' => $receivedCount === $totalChunks,
        ]);
    }

    /**
     * Abort and cleanup an in-progress upload.
     */
    public function abort(Request $request)
    {
        $request->validate([
            'uuid' => 'required|string|size:36',
            'fieldName' => 'required|string|in:path_obj,obj_file',
        ]);

        $this->cleanup($request->uuid, $request->fieldName);

        return response()->json(['aborted' => true]);
    }

    /**
     * Clean up old chunks (> MAX_CHUNK_AGE_HOURS).
     * Run via scheduler or on-demand.
     */
    public function cleanupOld()
    {
        $dir = self::CHUNK_DIR;
        if (! Storage::disk('local')->exists($dir)) {
            return;
        }

        $cutoff = now()->subHours(self::MAX_CHUNK_AGE_HOURS)->timestamp;
        $uuids = Storage::disk('local')->directories($dir);

        foreach ($uuids as $uuidDir) {
            foreach (Storage::disk('local')->directories($uuidDir) as $fieldDir) {
                foreach (Storage::disk('local')->files($fieldDir) as $file) {
                    if (Storage::disk('local')->lastModified($file) < $cutoff) {
                        Storage::disk('local')->delete($file);
                    }
                }

                // Delete the field directory if it is now empty
                $files = Storage::disk('local')->allFiles($fieldDir);
                $dirs = Storage::disk('local')->directories($fieldDir);
                if (empty($files) && empty($dirs)) {
                    Storage::disk('local')->deleteDirectory($fieldDir);
                }
            }

            // Delete the uuid directory if it is now empty
            $files = Storage::disk('local')->allFiles($uuidDir);
            $dirs = Storage::disk('local')->directories($uuidDir);
            if (empty($files) && empty($dirs)) {
                Storage::disk('local')->deleteDirectory($uuidDir);
            }
        }
    }

    // =======================
    // Private helpers
    // =======================

    private function getChunkDir(string $uuid, string $fieldName): string
    {
        return self::CHUNK_DIR.'/'.$uuid.'/'.$fieldName;
    }

    private function getSortedChunks(string $uuid, string $fieldName): Collection
    {
        $chunkDir = $this->getChunkDir($uuid, $fieldName);
        if (! Storage::disk('local')->exists($chunkDir)) {
            return collect();
        }

        return collect(Storage::disk('local')->files($chunkDir))
            ->filter(fn ($f) => str_starts_with(basename($f), 'chunk_'))
            ->sortBy(fn ($f) => (int) str_replace('chunk_', '', str_replace('.tmp', '', basename($f))));
    }

    private function getExpectedTotalChunks(string $uuid, string $fieldName): ?int
    {
        $metaFile = $this->getChunkDir($uuid, $fieldName).'/meta.txt';
        if (Storage::disk('local')->exists($metaFile)) {
            $meta = json_decode(Storage::disk('local')->get($metaFile), true);

            return $meta['totalChunks'] ?? null;
        }

        return null;
    }

    private function cleanup(string $uuid, string $fieldName): void
    {
        $chunkDir = $this->getChunkDir($uuid, $fieldName);
        if (Storage::disk('local')->exists($chunkDir)) {
            Storage::disk('local')->deleteDirectory($chunkDir);
        }

        // Clean up parent uuid directory if it is now empty
        $uuidDir = self::CHUNK_DIR.'/'.$uuid;
        if (Storage::disk('local')->exists($uuidDir)) {
            $files = Storage::disk('local')->allFiles($uuidDir);
            $directories = Storage::disk('local')->directories($uuidDir);
            if (empty($files) && empty($directories)) {
                Storage::disk('local')->deleteDirectory($uuidDir);
            }
        }
    }
}

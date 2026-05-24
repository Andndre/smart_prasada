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
        $chunkDirFull = storage_path($chunkDir);
        $chunkPath = $chunkDirFull.'/chunk_'.str_pad($chunkIndex, 6, '0', STR_PAD_LEFT).'.tmp';

        if (! is_dir($chunkDirFull)) {
            mkdir($chunkDirFull, 0755, true);
        }

        $file = $request->file('file');
        $fileSize = $file->getSize();
        $file->move($chunkDirFull, basename($chunkPath));

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
        $chunkDirFull = storage_path($chunkDir);
        $tempMergedPath = $chunkDirFull.'/merged_'.$uuid.'.tmp';

        $chunks = $this->getSortedChunks($uuid, $fieldName);

        if ($chunks->isEmpty()) {
            Log::warning('Chunked upload: no chunks found', [
                'uuid' => $uuid,
                'fieldName' => $fieldName,
                'chunkDirFull' => $chunkDirFull,
                'is_dir' => is_dir($chunkDirFull),
            ]);

            return response()->json(['error' => 'No chunks found.'], 404);
        }

        // Merge chunks
        $out = fopen($tempMergedPath, 'wb');
        if ($out === false) {
            return response()->json(['error' => 'Cannot create merged file.'], 500);
        }

        foreach ($chunks as $chunk) {
            $chunkPath = $chunk->getPathname();
            $chunkData = File::get($chunkPath);
            fwrite($out, $chunkData);
        }
        fclose($out);

        // Validate GLB magic bytes: first 4 bytes must be "glTF" (0x46546C67)
        $handle = fopen($tempMergedPath, 'rb');
        $header = fread($handle, 4);
        fclose($handle);

        if ($header !== 'glTF') {
            File::delete($tempMergedPath);
            $this->cleanup($uuid, $fieldName);

            return response()->json(['error' => 'File is not a valid GLB.'], 422);
        }

        // Move to final public storage location
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION) ?: 'glb';
        $filename = Str::uuid()->toString().'.'.strtolower($extension);
        $finalPath = "{$targetPath}/{$filename}";

        Storage::disk('public')->put($finalPath, File::get($tempMergedPath));
        $publicUrl = "/storage/{$finalPath}";

        // Cleanup
        File::delete($tempMergedPath);
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
        $dir = storage_path(self::CHUNK_DIR);
        if (! is_dir($dir)) {
            return;
        }

        $cutoff = now()->subHours(self::MAX_CHUNK_AGE_HOURS)->timestamp;
        $uuids = File::directories($dir);

        foreach ($uuids as $uuidDir) {
            foreach (File::directories($uuidDir) as $fieldDir) {
                foreach (File::files($fieldDir) as $file) {
                    if ($file->getMTime() < $cutoff) {
                        File::delete($file->getPathname());
                    }
                }
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
        $chunkDir = storage_path($this->getChunkDir($uuid, $fieldName));
        if (! is_dir($chunkDir)) {
            return collect();
        }

        return collect(File::files($chunkDir))
            ->filter(fn ($f) => str_starts_with($f->getFilename(), 'chunk_'))
            ->sortBy(fn ($f) => (int) str_replace('chunk_', '', str_replace('.tmp', '', $f->getFilename())));
    }

    private function getExpectedTotalChunks(string $uuid, string $fieldName): ?int
    {
        $metaFile = storage_path($this->getChunkDir($uuid, $fieldName).'/meta.txt');
        if (File::exists($metaFile)) {
            $meta = json_decode(File::get($metaFile), true);

            return $meta['totalChunks'] ?? null;
        }

        return null;
    }

    private function cleanup(string $uuid, string $fieldName): void
    {
        $chunkDir = storage_path($this->getChunkDir($uuid, $fieldName));
        if (is_dir($chunkDir)) {
            File::deleteDirectory($chunkDir);
        }
    }
}

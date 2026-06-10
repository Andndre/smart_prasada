<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

describe('Chunk Upload', function () {
    beforeEach(function () {
        Storage::fake('local');
    });

    describe('POST /admin/chunk-upload/chunk', function () {
        it('accepts chunk upload from admin', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $file = UploadedFile::fake()->create('model.glb', 1024);

            $response = $this->actingAs($admin)->post(route('admin.chunk-upload.chunk'), [
                'file' => $file,
                'uuid' => Str::uuid()->toString(),
                'chunkIndex' => 0,
                'totalChunks' => 1,
                'fieldName' => 'path_obj',
            ]);

            $response->assertJson(['received' => true]);
        });

        it('rejects chunk upload from non-admin users', function () {
            $user = User::factory()->create(['role' => 'user']);
            $file = UploadedFile::fake()->create('model.glb', 1024);

            $response = $this->actingAs($user)->post(route('admin.chunk-upload.chunk'), [
                'file' => $file,
                'uuid' => Str::uuid()->toString(),
                'chunkIndex' => 0,
                'totalChunks' => 1,
                'fieldName' => 'path_obj',
            ]);

            $response->assertRedirect(route('guest.home'));
        });
    });

    describe('POST /admin/chunk-upload/finalize', function () {
        it('finalizes chunk upload from admin', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $uuid = Str::uuid()->toString();

            $response = $this->actingAs($admin)->post(route('admin.chunk-upload.finalize'), [
                'uuid' => $uuid,
                'filename' => 'test-model.glb',
                'totalChunks' => 1,
                'fieldName' => 'path_obj',
            ]);

            // Will return error since no chunks exist - this is expected behavior
            $response->assertJsonFragment(['error' => 'No chunks found.']);
        });

        it('successfully merges chunks, puts final file to public disk, and cleans up temporary chunk directories', function () {
            Storage::fake('public');
            $admin = User::factory()->create(['role' => 'admin']);
            $uuid = Str::uuid()->toString();

            // Create fake chunk file starting with GLB magic header "glTF"
            $file = UploadedFile::fake()->createWithContent('chunk.tmp', "glTF\0\0\0\0chunk content");

            // Upload the chunk
            $this->actingAs($admin)->post(route('admin.chunk-upload.chunk'), [
                'file' => $file,
                'uuid' => $uuid,
                'chunkIndex' => 0,
                'totalChunks' => 1,
                'fieldName' => 'path_obj',
            ])->assertJson(['received' => true]);

            // Assert chunk directory exists in local fake
            Storage::disk('local')->assertExists('chunked-uploads/'.$uuid.'/path_obj/chunk_000000.tmp');

            // Finalize
            $response = $this->actingAs($admin)->post(route('admin.chunk-upload.finalize'), [
                'uuid' => $uuid,
                'fieldName' => 'path_obj',
                'original_filename' => 'model.glb',
            ]);

            $response->assertJsonStructure(['success', 'path', 'filename', 'url']);

            // Assert it moved to public disk
            $filename = $response->json('filename');
            Storage::disk('public')->assertExists('virtual-museum/models/'.$filename);

            // Assert local temporary chunk folder is completely deleted (including the parent uuid folder)
            Storage::disk('local')->assertMissing('chunked-uploads/'.$uuid);
        });
    });

    describe('GET /admin/chunk-upload/status', function () {
        it('returns chunk upload status to admin', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $uuid = Str::uuid()->toString();

            $response = $this->actingAs($admin)->get(route('admin.chunk-upload.status').'?uuid='.$uuid.'&fieldName=path_obj&totalChunks=1');

            $response->assertJson(['complete' => false]);
        });
    });

    describe('DELETE /admin/chunk-upload/abort', function () {
        it('aborts chunk upload from admin', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $uuid = Str::uuid()->toString();

            $response = $this->actingAs($admin)->delete(route('admin.chunk-upload.abort'), [
                'uuid' => $uuid,
                'fieldName' => 'path_obj',
            ]);

            $response->assertJson(['aborted' => true]);
        });

        it('cleans up all directories on abort', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $uuid = Str::uuid()->toString();

            $file = UploadedFile::fake()->create('chunk.tmp', 100);

            // Upload the chunk
            $this->actingAs($admin)->post(route('admin.chunk-upload.chunk'), [
                'file' => $file,
                'uuid' => $uuid,
                'chunkIndex' => 0,
                'totalChunks' => 1,
                'fieldName' => 'path_obj',
            ]);

            // Assert exists
            Storage::disk('local')->assertExists('chunked-uploads/'.$uuid.'/path_obj/chunk_000000.tmp');

            // Abort
            $this->actingAs($admin)->delete(route('admin.chunk-upload.abort'), [
                'uuid' => $uuid,
                'fieldName' => 'path_obj',
            ])->assertJson(['aborted' => true]);

            // Assert missing
            Storage::disk('local')->assertMissing('chunked-uploads/'.$uuid);
        });
    });
});

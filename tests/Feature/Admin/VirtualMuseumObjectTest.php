<?php

use App\Models\User;
use App\Models\VirtualMuseum;
use App\Models\VirtualMuseumObject;

describe('Virtual Museum Object Management', function () {
    describe('GET /admin/virtual-museum/{museum_id}/objects/create', function () {
        it('displays object create form', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $museum = VirtualMuseum::factory()->create();

            $response = $this->actingAs($admin)->get(route('admin.virtual-museum-object.create', $museum->museum_id));

            $response->assertStatus(200);
            $response->assertViewIs('admin.virtual-museum-object.create');
        });
    });

    describe('POST /admin/virtual-museum/{museum_id}/objects', function () {
        it('validates required fields', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $museum = VirtualMuseum::factory()->create();

            $response = $this->actingAs($admin)->post(route('admin.virtual-museum-object.store', $museum->museum_id), []);

            $response->assertSessionHasErrors(['nama']);
        });
    });

    describe('GET /admin/virtual-museum-object/{object_id}', function () {
        it('displays object details', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $object = VirtualMuseumObject::factory()->create();

            $response = $this->actingAs($admin)->get(route('admin.virtual-museum-object.show', $object->object_id));

            $response->assertStatus(200);
            $response->assertViewIs('admin.virtual-museum-object.show');
            $response->assertViewHas('object');
        });
    });

    describe('GET /admin/virtual-museum-object/{object_id}/edit', function () {
        it('displays object edit form', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $object = VirtualMuseumObject::factory()->create();

            $response = $this->actingAs($admin)->get(route('admin.virtual-museum-object.edit', $object->object_id));

            $response->assertStatus(200);
            $response->assertViewIs('admin.virtual-museum-object.edit');
            $response->assertViewHas('object');
        });
    });

    describe('PUT /admin/virtual-museum-object/{object_id}', function () {
        it('updates object successfully', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $object = VirtualMuseumObject::factory()->create();

            $response = $this->actingAs($admin)->put(route('admin.virtual-museum-object.update', $object->object_id), [
                'nama' => 'Updated Object Name',
                'deskripsi' => 'Updated deskripsi',
            ]);

            $response->assertRedirect(route('admin.virtual-museum-object.show', $object->object_id));
            $response->assertSessionHas('success');
            $this->assertDatabaseHas('virtual_museum_object', [
                'object_id' => $object->object_id,
                'nama' => 'Updated Object Name',
            ]);
        });
    });

    describe('DELETE /admin/virtual-museum-object/{object_id}', function () {
        it('deletes object successfully', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $object = VirtualMuseumObject::factory()->create();
            $museumId = $object->museum_id;

            $response = $this->actingAs($admin)->delete(route('admin.virtual-museum-object.destroy', $object->object_id));

            $response->assertRedirect(route('admin.virtual-museum.show', $museumId));
            $response->assertSessionHas('success');
            $this->assertDatabaseMissing('virtual_museum_object', ['object_id' => $object->object_id]);
        });
    });
});

describe('VR Editor', function () {
    describe('GET /admin/virtual-museum/{museum_id}/editor', function () {
        it('displays the visual editor for admin', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $museum = VirtualMuseum::factory()->create();
            VirtualMuseumObject::factory()->create([
                'museum_id' => $museum->museum_id,
                'mesh_name' => 'Mesh_Editor_Test',
            ]);

            $response = $this->actingAs($admin)->get(route('admin.virtual-museum.editor', $museum->museum_id));

            $response->assertStatus(200);
            $response->assertViewIs('admin.virtual-museum.editor');
            $response->assertSee('Mesh_Editor_Test');
        });

        it('is not accessible for regular users', function () {
            $user = User::factory()->create();
            $museum = VirtualMuseum::factory()->create();

            $this->actingAs($user)
                ->get(route('admin.virtual-museum.editor', $museum->museum_id))
                ->assertRedirect();
        });
    });

    describe('POST /admin/virtual-museum/{museum_id}/editor/objects', function () {
        it('creates a new object keyed by mesh_name', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $museum = VirtualMuseum::factory()->create();

            $response = $this->actingAs($admin)->postJson(route('admin.virtual-museum.editor.save', $museum->museum_id), [
                'nama' => 'Arca Baru',
                'mesh_name' => 'Arca_Baru',
                'slot_mesh_name' => 'Slot_Arca_Baru',
                'deskripsi' => 'Dibuat dari editor.',
            ]);

            $response->assertSuccessful();
            $this->assertDatabaseHas('virtual_museum_object', [
                'museum_id' => $museum->museum_id,
                'situs_id' => $museum->situs_id,
                'mesh_name' => 'Arca_Baru',
                'slot_mesh_name' => 'Slot_Arca_Baru',
            ]);
        });

        it('updates existing object with the same mesh_name instead of duplicating', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $museum = VirtualMuseum::factory()->create();
            $object = VirtualMuseumObject::factory()->create([
                'museum_id' => $museum->museum_id,
                'situs_id' => $museum->situs_id,
                'mesh_name' => 'Arca_Lama',
            ]);

            $response = $this->actingAs($admin)->postJson(route('admin.virtual-museum.editor.save', $museum->museum_id), [
                'nama' => 'Nama Diperbarui',
                'mesh_name' => 'Arca_Lama',
            ]);

            $response->assertSuccessful();
            expect($response->json('object_id'))->toBe($object->object_id);
            expect(VirtualMuseumObject::where('mesh_name', 'Arca_Lama')->count())->toBe(1);
            $this->assertDatabaseHas('virtual_museum_object', [
                'object_id' => $object->object_id,
                'nama' => 'Nama Diperbarui',
            ]);
        });

        it('validates required fields', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $museum = VirtualMuseum::factory()->create();

            $this->actingAs($admin)
                ->postJson(route('admin.virtual-museum.editor.save', $museum->museum_id), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['nama', 'mesh_name']);
        });
    });
});

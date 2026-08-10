<?php

use App\Enums\NilaiKarakter;
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

describe('Nilai Karakter', function () {
    it('stores multiple values from the admin create form', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $museum = VirtualMuseum::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.virtual-museum-object.store', $museum->museum_id), [
                'nama' => 'Punden Berundak Utama',
                'mesh_name' => 'Punden_Berundak_Utama',
                'nilai_karakter' => [
                    NilaiKarakter::Religius->value,
                    NilaiKarakter::GotongRoyong->value,
                ],
            ])
            ->assertRedirect();

        $object = VirtualMuseumObject::where('mesh_name', 'Punden_Berundak_Utama')->sole();
        expect($object->nilai_karakter)->toBe(['religius', 'gotong_royong']);
    });

    it('stores an empty list when no value is checked', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $museum = VirtualMuseum::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.virtual-museum-object.store', $museum->museum_id), [
                'nama' => 'Objek Tanpa Nilai',
                'mesh_name' => 'Objek_Tanpa_Nilai',
            ])
            ->assertRedirect();

        expect(VirtualMuseumObject::where('mesh_name', 'Objek_Tanpa_Nilai')->sole()->nilai_karakter)->toBe([]);
    });

    it('rejects a value outside the enum', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $museum = VirtualMuseum::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.virtual-museum-object.store', $museum->museum_id), [
                'nama' => 'Objek Nilai Palsu',
                'nilai_karakter' => ['nilai_yang_tidak_ada'],
            ])
            ->assertSessionHasErrors('nilai_karakter.0');

        expect(VirtualMuseumObject::where('nama', 'Objek Nilai Palsu')->exists())->toBeFalse();
    });

    it('updates values from the edit form', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $object = VirtualMuseumObject::factory()->create([
            'nilai_karakter' => [NilaiKarakter::Religius->value],
        ]);

        $this->actingAs($admin)
            ->put(route('admin.virtual-museum-object.update', $object->object_id), [
                'nama' => $object->nama,
                'nilai_karakter' => [NilaiKarakter::BernalarKritis->value],
            ])
            ->assertRedirect();

        expect($object->fresh()->nilai_karakter)->toBe(['bernalar_kritis']);
    });

    it('saves values through the visual editor endpoint', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $museum = VirtualMuseum::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.virtual-museum.editor.save', $museum->museum_id), [
                'nama' => 'Padma Kurung',
                'mesh_name' => 'Padma_Kurung',
                'nilai_karakter' => [NilaiKarakter::Religius->value],
            ])
            ->assertSuccessful();

        expect(VirtualMuseumObject::where('mesh_name', 'Padma_Kurung')->sole()->nilai_karakter)
            ->toBe(['religius']);
    });

    it('rejects an invalid value through the visual editor endpoint', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $museum = VirtualMuseum::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.virtual-museum.editor.save', $museum->museum_id), [
                'nama' => 'Padma Kurung',
                'mesh_name' => 'Padma_Kurung',
                'nilai_karakter' => ['bukan_nilai'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nilai_karakter.0']);
    });

    it('sends values to the VR scene', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();
        VirtualMuseumObject::factory()->create([
            'museum_id' => $museum->museum_id,
            'situs_id' => $museum->situs_id,
            'mesh_name' => 'Motif_Ceplok_Bunga',
            'nilai_karakter' => [NilaiKarakter::Kreatif->value],
        ]);

        $response = $this->actingAs($user)
            ->get(route('vr.museum', [$museum->situs_id, $museum->museum_id]));

        $response->assertSuccessful();
        expect($response->viewData('vrObjects')->firstWhere('mesh_name', 'Motif_Ceplok_Bunga')->nilai_karakter)
            ->toBe(['kreatif']);
    });
});

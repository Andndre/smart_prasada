<?php

namespace Tests\Feature\User;

use App\Models\MuseumUserVisit;
use App\Models\SitusPeninggalan;
use App\Models\User;
use App\Models\VirtualMuseum;
use App\Models\VirtualMuseumObject;

test('guest is redirected to login when visiting vr map', function () {
    $this->get(route('guest.vr.maps'))->assertRedirect(route('login'));
});

test('vr map only lists situs with a museum model', function () {
    $user = User::factory()->create(['level_sekarang' => 1, 'progress_level_sekarang' => 1]);

    $situsWithModel = SitusPeninggalan::factory()->create(['nama' => 'Situs Ber-VR', 'user_id' => $user->id]);
    VirtualMuseum::factory()->create(['situs_id' => $situsWithModel->situs_id]);

    SitusPeninggalan::factory()->create(['nama' => 'Situs Tanpa VR', 'user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('guest.vr.maps'));

    $response->assertStatus(200);
    $response->assertSee('Situs Ber-VR');
    $response->assertDontSee('Situs Tanpa VR');
});

test('authenticated user can open vr museum page for a valid museum', function () {
    $user = User::factory()->create(['level_sekarang' => 1, 'progress_level_sekarang' => 1]);
    $situs = SitusPeninggalan::factory()->create(['user_id' => $user->id]);
    $museum = VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

    $response = $this->actingAs($user)->get(route('vr.museum', [
        'situs_id' => $situs->situs_id,
        'museum_id' => $museum->museum_id,
    ]));

    $response->assertStatus(200);
    $response->assertSee($situs->nama);
    $response->assertSee((string) $museum->museum_id, false);
});

test('vr museum page exposes only objects with a mesh name', function () {
    $user = User::factory()->create(['level_sekarang' => 1, 'progress_level_sekarang' => 1]);
    $situs = SitusPeninggalan::factory()->create(['user_id' => $user->id]);
    $museum = VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

    $baseAttributes = [
        'situs_id' => $situs->situs_id,
        'museum_id' => $museum->museum_id,
        'gambar_real' => 'objects/images/dummy.jpg',
        'path_obj' => 'objects/models/dummy.glb',
    ];

    VirtualMuseumObject::create($baseAttributes + [
        'nama' => 'Lukisan Barong',
        'mesh_name' => 'Lukisan_Barong',
        'deskripsi' => 'Lukisan klasik Bali.',
    ]);
    VirtualMuseumObject::create($baseAttributes + [
        'nama' => 'Objek Tanpa Mesh',
        'deskripsi' => 'Tidak interaktif di VR.',
    ]);

    $response = $this->actingAs($user)->get(route('vr.museum', [
        'situs_id' => $situs->situs_id,
        'museum_id' => $museum->museum_id,
    ]));

    $response->assertStatus(200);
    $response->assertSee('Lukisan_Barong');
    $response->assertDontSee('Objek Tanpa Mesh');
});

test('visiting vr museum records a museum visit', function () {
    $user = User::factory()->create(['level_sekarang' => 1, 'progress_level_sekarang' => 1]);
    $situs = SitusPeninggalan::factory()->create(['user_id' => $user->id]);
    $museum = VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

    $this->actingAs($user)->get(route('vr.museum', [
        'situs_id' => $situs->situs_id,
        'museum_id' => $museum->museum_id,
    ]))->assertStatus(200);

    expect(MuseumUserVisit::where('user_id', $user->id)
        ->where('museum_id', $museum->museum_id)
        ->exists())->toBeTrue();
});

test('vr museum route 404s when museum does not belong to situs', function () {
    $user = User::factory()->create(['level_sekarang' => 1, 'progress_level_sekarang' => 1]);
    $situsA = SitusPeninggalan::factory()->create(['user_id' => $user->id]);
    $situsB = SitusPeninggalan::factory()->create(['user_id' => $user->id]);
    $museum = VirtualMuseum::factory()->create(['situs_id' => $situsB->situs_id]);

    $response = $this->actingAs($user)->get(route('vr.museum', [
        'situs_id' => $situsA->situs_id,
        'museum_id' => $museum->museum_id,
    ]));

    $response->assertNotFound();
});

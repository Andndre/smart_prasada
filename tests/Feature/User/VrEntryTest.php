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

test('vr museum nav keeps the id the session hides it by', function () {
    $user = User::factory()->create(['level_sekarang' => 1, 'progress_level_sekarang' => 1]);
    $situs = SitusPeninggalan::factory()->create(['user_id' => $user->id]);
    $museum = VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

    $url = route('vr.museum', ['situs_id' => $situs->situs_id, 'museum_id' => $museum->museum_id]);

    $this->actingAs($user)->get($url)->assertSee('id="vr-nav"', false);
    $this->actingAs($user)->get($url.'?kiosk=1')->assertDontSee('id="vr-nav"', false);
});

test('vr museum page ships a valid importmap for every vr module', function () {
    $user = User::factory()->create(['level_sekarang' => 1, 'progress_level_sekarang' => 1]);
    $situs = SitusPeninggalan::factory()->create(['user_id' => $user->id]);
    $museum = VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

    $html = $this->actingAs($user)->get(route('vr.museum', [
        'situs_id' => $situs->situs_id,
        'museum_id' => $museum->museum_id,
    ]))->getContent();

    expect(preg_match('/<script type="importmap">(.*?)<\/script>/s', $html, $matches))->toBe(1);

    $imports = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR)['imports'];

    foreach (['vr-phases', 'vr-responden', 'vr-events', 'vr-panels', 'vr-controls', 'vr-sesi', 'vr-hp', 'vr-petunjuk'] as $modul) {
        expect($imports)->toHaveKey($modul);
    }
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
        'posisi_awal' => [1.5, 0.0, -2.25],
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
    // Posisi lepas potongan puzzle ikut ke klien — runtime tidak punya sumber lain.
    $response->assertSee('-2.25');
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

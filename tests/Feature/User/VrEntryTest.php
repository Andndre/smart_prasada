<?php

namespace Tests\Feature\User;

use App\Models\SitusPeninggalan;
use App\Models\User;
use App\Models\VirtualMuseum;

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

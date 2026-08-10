<?php

use App\Helper\TokenHelper;
use App\Models\SitusPeninggalan;
use App\Models\User;
use App\Models\VirtualMuseum;

describe('GET /vr/peluncur/{museum_id}', function () {
    it('shows the launcher with a token for the logged-in facilitator', function () {
        $fasilitator = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();

        $response = $this->actingAs($fasilitator)->get(route('vr.peluncur', $museum->museum_id));

        $response->assertSuccessful();
        $response->assertViewIs('guest.vr.peluncur');

        $token = $response->viewData('arToken');
        expect(TokenHelper::verify($token)['user_id'])->toBe($fasilitator->id);
    });

    it('never mints a token for anyone but the current user', function () {
        $fasilitator = User::factory()->create();
        $korban = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();

        // Parameter user_id yang dikarang harus diabaikan sepenuhnya.
        $response = $this->actingAs($fasilitator)
            ->get(route('vr.peluncur', $museum->museum_id).'?user_id='.$korban->id);

        expect(TokenHelper::verify($response->viewData('arToken'))['user_id'])
            ->toBe($fasilitator->id);
    });

    it('is not accessible to guests', function () {
        $museum = VirtualMuseum::factory()->create();

        $this->get(route('vr.peluncur', $museum->museum_id))->assertRedirect(route('login'));
    });
});

describe('mode kiosk pada halaman VR', function () {
    it('hides the app navigation when kiosk is on', function () {
        $user = User::factory()->create();
        $situs = SitusPeninggalan::factory()->create();
        $museum = VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

        $this->actingAs($user)
            ->get(route('vr.museum', [$situs->situs_id, $museum->museum_id]).'?kiosk=1')
            ->assertSuccessful()
            ->assertDontSee(route('guest.situs.detail', $situs->situs_id));
    });

    it('shows the app navigation without the kiosk flag', function () {
        $user = User::factory()->create();
        $situs = SitusPeninggalan::factory()->create();
        $museum = VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

        $this->actingAs($user)
            ->get(route('vr.museum', [$situs->situs_id, $museum->museum_id]))
            ->assertSuccessful()
            ->assertSee(route('guest.situs.detail', $situs->situs_id));
    });

    it('carries kiosk and kode_akhir through the token redirect', function () {
        $user = User::factory()->create();
        $situs = SitusPeninggalan::factory()->create();
        $museum = VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

        $response = $this->get(
            route('vr.museum', [$situs->situs_id, $museum->museum_id]).
            '?arToken='.TokenHelper::generate($user->id).'&kode=R041&kode_akhir=R060&kiosk=1'
        );

        $tujuan = $response->headers->get('Location');
        expect($tujuan)->toContain('kode=R041');
        expect($tujuan)->toContain('kode_akhir=R060');
        expect($tujuan)->toContain('kiosk=1');
        expect($tujuan)->not->toContain('arToken');
    });
});

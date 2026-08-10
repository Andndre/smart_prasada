<?php

use App\Helper\TokenHelper;
use App\Models\SitusPeninggalan;
use App\Models\User;
use App\Models\VirtualMuseum;

/**
 * Menembak jalur handoff QR yang sebenarnya, bukan endpoint event secara langsung.
 *
 * Fasilitator membuka scene lewat tautan/QR berisi arToken DAN kode responden. Token
 * ditukar sesi lalu di-redirect agar hilang dari URL — dan di situlah query string
 * sempat ikut terbuang, membuat setiap baris vr_event kehilangan kode_responden tanpa
 * gejala apa pun di lokal (di dev kita sudah login, jadi tidak ada redirect sama sekali).
 */
describe('QR handoff ke scene VR', function () {
    it('keeps other query parameters when trading the token for a session', function () {
        $user = User::factory()->create();
        $situs = SitusPeninggalan::factory()->create();
        $museum = VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

        $response = $this->get(route('vr.museum', [$situs->situs_id, $museum->museum_id]).
            '?arToken='.TokenHelper::generate($user->id).'&kode=R042');

        $response->assertRedirect();
        $tujuan = $response->headers->get('Location');

        expect($tujuan)->toContain('kode=R042');
        expect($tujuan)->not->toContain('arToken');
        $this->assertAuthenticatedAs($user);
    });

    it('redirects without a query string when the token was the only parameter', function () {
        $user = User::factory()->create();
        $situs = SitusPeninggalan::factory()->create();
        $museum = VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

        $response = $this->get(route('vr.museum', [$situs->situs_id, $museum->museum_id]).
            '?arToken='.TokenHelper::generate($user->id));

        $response->assertRedirect(route('vr.museum', [$situs->situs_id, $museum->museum_id]));
    });

    it('delivers the responden code to the scene after the redirect is followed', function () {
        $user = User::factory()->create();
        $situs = SitusPeninggalan::factory()->create();
        $museum = VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

        $response = $this->followingRedirects()->get(
            route('vr.museum', [$situs->situs_id, $museum->museum_id]).
            '?arToken='.TokenHelper::generate($user->id).'&kode=R042'
        );

        $response->assertSuccessful();
        // vr-museum.js membaca kode dari location.search, jadi yang menentukan adalah
        // URL akhir yang dilihat browser — bukan URL yang diklik. app('request') di sini
        // adalah request terakhir yang benar-benar dilayani, yaitu hasil redirect.
        expect($this->app['request']->query('kode'))->toBe('R042');
        expect($this->app['request']->query('arToken'))->toBeNull();
    });
});

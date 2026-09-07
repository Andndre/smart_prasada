<?php

use App\Helper\TokenHelper;
use App\Models\SitusPeninggalan;
use App\Models\User;
use App\Models\VirtualMuseum;
use Illuminate\Support\Facades\Cache;

describe('Kiosk PIN Pairing System', function () {
    it('allows anyone to access GET /kiosk without login', function () {
        $response = $this->get(route('kiosk.entry'));

        $response->assertSuccessful();
        $response->assertViewIs('guest.vr.kiosk-entry');
        $response->assertSee('Mode Kiosk VR');
    });

    it('generates a 4-digit PIN when facilitator opens launcher and caches session metadata', function () {
        $user = User::factory()->create();
        $situs = SitusPeninggalan::factory()->create();
        $museum = VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

        $response = $this->actingAs($user)->get(route('vr.peluncur', $museum->museum_id));

        $response->assertSuccessful();
        $pin = $response->viewData('pin');

        expect($pin)->toBeString();
        expect(strlen($pin))->toBe(4);
        expect(is_numeric($pin))->toBeTrue();

        $cached = Cache::get('kiosk_pin_'.$pin);
        expect($cached)->not->toBeNull();
        expect($cached['museum_id'])->toBe($museum->museum_id);
        expect($cached['situs_id'])->toBe($situs->situs_id);
        expect($cached['kode'])->toBe('R001');
        expect($cached['kiosk'])->toBeTrue();
    });

    it('redirects headset to the VR museum with arToken and kiosk flags on valid PIN', function () {
        $user = User::factory()->create();
        $situs = SitusPeninggalan::factory()->create();
        $museum = VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);
        $token = TokenHelper::generate($user->id);

        $pin = '5678';
        Cache::put('kiosk_pin_'.$pin, [
            'museum_id' => $museum->museum_id,
            'situs_id' => $situs->situs_id,
            'user_id' => $user->id,
            'arToken' => $token,
            'kode' => 'R012',
            'kode_akhir' => 'R030',
            'kiosk' => true,
        ], now()->addMinutes(30));

        $response = $this->post(route('kiosk.verify'), ['pin' => $pin]);

        $response->assertRedirect();
        $target = $response->headers->get('Location');

        expect($target)->toContain('arToken='.urlencode($token));
        expect($target)->toContain('kode=R012');
        expect($target)->toContain('kode_akhir=R030');
        expect($target)->toContain('kiosk=1');
        expect($target)->toContain(route('vr.museum', [$situs->situs_id, $museum->museum_id]));
    });

    it('rejects an invalid or expired PIN with error', function () {
        $response = $this->from(route('kiosk.entry'))
            ->post(route('kiosk.verify'), ['pin' => '9999']);

        $response->assertRedirect(route('kiosk.entry'));
        $response->assertSessionHas('error');
    });

    it('validates that pin must be 4 digits', function () {
        $response = $this->from(route('kiosk.entry'))
            ->post(route('kiosk.verify'), ['pin' => '12']);

        $response->assertRedirect(route('kiosk.entry'));
        $response->assertSessionHasErrors('pin');
    });

    it('syncs updated respondent code and kiosk state from facilitator form', function () {
        $fasilitator = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();
        $pin = '4321';

        Cache::put('kiosk_pin_'.$pin, [
            'museum_id' => $museum->museum_id,
            'situs_id' => $museum->situs_id,
            'user_id' => $fasilitator->id,
            'arToken' => TokenHelper::generate($fasilitator->id),
            'kode' => 'R001',
            'kode_akhir' => null,
            'kiosk' => true,
        ], now()->addMinutes(30));

        $response = $this->actingAs($fasilitator)->postJson(route('vr.peluncur.pin.update'), [
            'pin' => $pin,
            'kode' => 'R055',
            'kode_akhir' => 'R080',
            'kiosk' => true,
        ]);

        $response->assertSuccessful();
        $response->assertJson(['status' => 'ok']);

        $updated = Cache::get('kiosk_pin_'.$pin);
        expect($updated['kode'])->toBe('R055');
        expect($updated['kode_akhir'])->toBe('R080');
        expect($updated['kiosk'])->toBeTrue();
    });
});

<?php

namespace Tests\Feature;

use App\Models\Ebook;
use App\Models\Era;
use App\Models\Materi;
use App\Models\Posttest;
use App\Models\Pretest;
use App\Models\SitusPeninggalan;
use App\Models\User;
use App\Models\VirtualMuseum;

describe('Demo Mode Active (config app.demo_mode = true)', function () {
    beforeEach(function () {
        config(['app.demo_mode' => true]);
    });

    test('vr map unlocks all situs in demo mode regardless of user level', function () {
        $user = User::factory()->create(['level_sekarang' => 0, 'progress_level_sekarang' => 0]);

        $materi = Materi::factory()->create(['urutan' => 5]);
        $situs = SitusPeninggalan::factory()->create(['materi_id' => $materi->materi_id, 'user_id' => $user->id]);
        VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

        $response = $this->actingAs($user)->get(route('guest.vr.maps'));

        $response->assertSuccessful();
        $unlockedIds = $response->viewData('unlockedSitusIds');
        expect($unlockedIds)->toContain($situs->situs_id);
    });

    test('elearning index marks all materi available in demo mode', function () {
        $user = User::factory()->create(['level_sekarang' => 0, 'progress_level_sekarang' => 0]);
        $era = Era::factory()->create();
        $materi1 = Materi::factory()->create(['era_id' => $era->era_id, 'urutan' => 1]);
        $materi2 = Materi::factory()->create(['era_id' => $era->era_id, 'urutan' => 2]);

        $response = $this->actingAs($user)->get(route('guest.elearning'));

        $response->assertSuccessful();
        $eraCards = $response->viewData('eraCards');
        expect($eraCards->first()->is_available)->toBeTrue();
    });

    test('elearning era materi list marks all materi available in demo mode', function () {
        $user = User::factory()->create(['level_sekarang' => 0, 'progress_level_sekarang' => 0]);
        $era = Era::factory()->create();
        $materi = Materi::factory()->create(['era_id' => $era->era_id, 'urutan' => 99]);

        $response = $this->actingAs($user)->get(route('guest.elearning.era', $era->era_id));

        $response->assertSuccessful();
        $materis = $response->viewData('materis');
        expect($materis->first()->is_available)->toBeTrue();
    });

    test('elearning materi detail opens all tabs in demo mode', function () {
        $user = User::factory()->create(['level_sekarang' => 0, 'progress_level_sekarang' => 0]);
        $materi = Materi::factory()->create();

        $response = $this->actingAs($user)->get(route('guest.elearning.materi', $materi->materi_id));

        $response->assertSuccessful();
        expect($response->viewData('ebook_available'))->toBeTrue();
        expect($response->viewData('museum_available'))->toBeTrue();
        expect($response->viewData('posttest_available'))->toBeTrue();
    });

    test('situs detail reports is_unlocked true in demo mode', function () {
        $user = User::factory()->create(['level_sekarang' => 0, 'progress_level_sekarang' => 0]);
        $materi = Materi::factory()->create(['urutan' => 10]);
        $situs = SitusPeninggalan::factory()->create(['materi_id' => $materi->materi_id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('guest.situs.detail', $situs->situs_id));

        $response->assertSuccessful();
        expect($response->viewData('is_unlocked'))->toBeTrue();
    });

    test('pretest submission does not increment user progress in demo mode', function () {
        $user = User::factory()->create(['level_sekarang' => 0, 'progress_level_sekarang' => 0]);
        $materi = Materi::factory()->create();
        $pretest = Pretest::factory()->create(['materi_id' => $materi->materi_id]);

        $this->actingAs($user)->post(route('guest.elearning.pretest.submit', $materi->materi_id), [
            'answers' => [$pretest->pretest_id => 'A'],
        ])->assertSessionHasNoErrors();

        $user->refresh();
        expect($user->progress_level_sekarang)->toBe(0);
        expect($user->level_sekarang)->toBe(0);
    });

    test('posttest submission does not increment user progress in demo mode', function () {
        $user = User::factory()->create(['level_sekarang' => 0, 'progress_level_sekarang' => 3]);
        $materi = Materi::factory()->create();
        $posttest = Posttest::factory()->create(['materi_id' => $materi->materi_id]);

        $this->actingAs($user)->post(route('guest.elearning.posttest.submit', $materi->materi_id), [
            'answers' => [$posttest->posttest_id => 'A'],
        ])->assertSessionHasNoErrors();

        $user->refresh();
        expect($user->progress_level_sekarang)->toBe(3);
        expect($user->level_sekarang)->toBe(0);
    });

    test('mark ebook read does not increment user progress in demo mode', function () {
        $user = User::factory()->create(['level_sekarang' => 0, 'progress_level_sekarang' => 1]);
        $materi = Materi::factory()->create();
        $ebook = Ebook::factory()->create(['materi_id' => $materi->materi_id]);

        $this->actingAs($user)->post(route('guest.elearning.ebook.read', $ebook->ebook_id))
            ->assertSuccessful();

        $user->refresh();
        expect($user->progress_level_sekarang)->toBe(1);
    });

    test('vr museum visit does not increment user progress in demo mode', function () {
        $user = User::factory()->create(['level_sekarang' => 0, 'progress_level_sekarang' => 2]);
        $materi = Materi::factory()->create();
        $situs = SitusPeninggalan::factory()->create(['materi_id' => $materi->materi_id, 'user_id' => $user->id]);
        $museum = VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

        $this->actingAs($user)->get(route('vr.museum', [
            'situs_id' => $situs->situs_id,
            'museum_id' => $museum->museum_id,
        ]))->assertSuccessful();

        $user->refresh();
        expect($user->progress_level_sekarang)->toBe(2);
    });

    test('materi shouldIncrementProgress returns false in demo mode', function () {
        $user = User::factory()->create(['level_sekarang' => 0, 'progress_level_sekarang' => 0]);
        $materi = Materi::factory()->create();

        expect($materi->shouldIncrementProgress($user, 1))->toBeFalse();
    });
});

describe('Normal Learning Mode (config app.demo_mode = false)', function () {
    beforeEach(function () {
        config(['app.demo_mode' => false]);
    });

    test('vr map locks situs when user has not reached the museum step', function () {
        $user = User::factory()->create(['level_sekarang' => 0, 'progress_level_sekarang' => 0]);

        $materi = Materi::factory()->create();
        $situs = SitusPeninggalan::factory()->create(['materi_id' => $materi->materi_id, 'user_id' => $user->id]);
        VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

        $response = $this->actingAs($user)->get(route('guest.vr.maps'));

        $unlockedIds = $response->viewData('unlockedSitusIds');
        expect($unlockedIds)->not->toContain($situs->situs_id);
    });

    test('vr map unlocks situs when user has completed the ebook step for current materi', function () {
        $user = User::factory()->create(['level_sekarang' => 0, 'progress_level_sekarang' => User::EBOOK]);

        $materi = Materi::factory()->create();
        $situs = SitusPeninggalan::factory()->create(['materi_id' => $materi->materi_id, 'user_id' => $user->id]);
        VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

        $response = $this->actingAs($user)->get(route('guest.vr.maps'));

        $unlockedIds = $response->viewData('unlockedSitusIds');
        expect($unlockedIds)->toContain($situs->situs_id);
    });

    test('vr map unlocks situs when materi is already completed', function () {
        $user = User::factory()->create(['level_sekarang' => 1, 'progress_level_sekarang' => 0]);

        $materi = Materi::factory()->create();
        $situs = SitusPeninggalan::factory()->create(['materi_id' => $materi->materi_id, 'user_id' => $user->id]);
        VirtualMuseum::factory()->create(['situs_id' => $situs->situs_id]);

        $response = $this->actingAs($user)->get(route('guest.vr.maps'));

        $unlockedIds = $response->viewData('unlockedSitusIds');
        expect($unlockedIds)->toContain($situs->situs_id);
    });

    test('pretest submission increments progress in normal mode', function () {
        $user = User::factory()->create(['level_sekarang' => 0, 'progress_level_sekarang' => 0]);
        $materi = Materi::factory()->create();
        $pretest = Pretest::factory()->create(['materi_id' => $materi->materi_id]);

        $this->actingAs($user)->post(route('guest.elearning.pretest.submit', $materi->materi_id), [
            'answers' => [$pretest->pretest_id => 'A'],
        ])->assertSessionHasNoErrors();

        $user->refresh();
        expect($user->progress_level_sekarang)->toBe(1);
    });
});

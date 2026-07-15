<?php

namespace Tests\Feature\User;

use App\Models\User;

test('pengaturan page renders successfully', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->get('/pengaturan');

    $response->assertStatus(200);
});

test('panduan page renders successfully', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->get('/panduan');

    $response->assertStatus(200);
});

test('unauthenticated user is redirected to login on pengaturan', function () {
    $this->get('/pengaturan')->assertRedirect('/login');
});

test('unauthenticated user is redirected to login on panduan', function () {
    $this->get('/panduan')->assertRedirect('/login');
});

test('user can change language and it queues a cookie', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->get('/language/en');

    $response->assertSessionHas('locale', 'en');
    $response->assertCookie('locale', 'en');
});

test('locale cookie is used to set session locale when session is empty', function () {
    $user = User::factory()->create(['role' => 'user']);

    // Call /pengaturan with the locale cookie set to 'en'
    $response = $this->actingAs($user)
        ->withCookie('locale', 'en')
        ->get('/pengaturan');

    $response->assertSessionHas('locale', 'en');
    expect(app()->getLocale())->toBe('en');
});

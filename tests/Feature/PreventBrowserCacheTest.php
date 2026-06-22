<?php

it('sets no-cache headers on web responses', function () {
    $response = $this->get(route('login'));

    $response->assertHeader('Cache-Control');
    expect($response->headers->get('Cache-Control'))->toContain('no-store')
        ->toContain('no-cache')
        ->toContain('max-age=0');
    $response->assertHeader('Pragma', 'no-cache');
    $response->assertHeader('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
});

it('sets no-cache headers on authenticated pages', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertHeader('Cache-Control');
    expect($response->headers->get('Cache-Control'))->toContain('no-store')
        ->toContain('no-cache')
        ->toContain('max-age=0');
    $response->assertHeader('Pragma', 'no-cache');
});

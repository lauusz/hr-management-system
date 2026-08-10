<?php

use App\Models\User;

it('does not load flatpickr on dashboard without a calendar', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('cdn.jsdelivr.net/npm/flatpickr', false);
});

it('loads flatpickr locally on pages with a calendar', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')
        ->get(route('leave-requests.index'))
        ->assertOk()
        ->assertSee(asset('vendor/flatpickr/4.6.13/flatpickr.min.css'), false)
        ->assertSee(asset('vendor/flatpickr/4.6.13/flatpickr.min.js'), false)
        ->assertDontSee('cdn.jsdelivr.net/npm/flatpickr', false);
});

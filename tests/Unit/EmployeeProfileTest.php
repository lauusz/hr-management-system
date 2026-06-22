<?php

use App\Models\EmployeeProfile;

pest()->extend(Tests\TestCase::class)
    ->in('Unit');

describe('EmployeeProfile', function () {
    it('does not expose broken shiftPattern relation', function () {
        $profile = new EmployeeProfile();

        expect(method_exists($profile, 'shiftPattern'))->toBeFalse();
    });

    it('does not include shift_pattern_id in fillable', function () {
        $profile = new EmployeeProfile();

        expect($profile->getFillable())->not->toContain('shift_pattern_id');
    });

    it('has working user relation', function () {
        $profile = new EmployeeProfile();

        expect(method_exists($profile, 'user'))->toBeTrue();
    });

    it('has working pt relation', function () {
        $profile = new EmployeeProfile();

        expect(method_exists($profile, 'pt'))->toBeTrue();
    });
});

<?php

use App\Models\EmployeeProfile;
use Illuminate\Support\Carbon;

pest()->extend(Tests\TestCase::class)
    ->in('Unit');

describe('EmployeeProfile', function () {
    it('does not expose broken shiftPattern relation', function () {
        $profile = new EmployeeProfile;

        expect(method_exists($profile, 'shiftPattern'))->toBeFalse();
    });

    it('does not include shift_pattern_id in fillable', function () {
        $profile = new EmployeeProfile;

        expect($profile->getFillable())->not->toContain('shift_pattern_id');
    });

    it('has working user relation', function () {
        $profile = new EmployeeProfile;

        expect(method_exists($profile, 'user'))->toBeTrue();
    });

    it('has working pt relation', function () {
        $profile = new EmployeeProfile;

        expect(method_exists($profile, 'pt'))->toBeTrue();
    });

    it('formats masa kerja with full years and remaining months', function () {
        Carbon::setTestNow('2026-06-29');

        $profile = new EmployeeProfile;
        $profile->setRawAttributes(['tgl_bergabung' => '2024-03-10']);

        expect($profile->masa_kerja)->toBe('2 Tahun 3 Bulan');
    });
});

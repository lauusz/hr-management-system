<?php

use App\Models\Pt;
use Database\Seeders\ImportEmployeesFromCsvSeeder;

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

describe('ImportEmployeesFromCsvSeeder', function () {
    it('maps PT name to existing or newly created pt_id', function () {
        $seeder = new ImportEmployeesFromCsvSeeder;
        $method = new ReflectionMethod($seeder, 'resolvePtId');
        $method->setAccessible(true);

        $first = $method->invoke($seeder, 'PT Maju Jaya');
        $second = $method->invoke($seeder, 'PT Maju Jaya');

        $pt = Pt::where('name', 'PT Maju Jaya')->first();
        expect($pt)->not->toBeNull()
            ->and($first)->toBe($pt->id)
            ->and($second)->toBe($pt->id);
    });

    it('returns null for empty or missing PT name', function () {
        $seeder = new ImportEmployeesFromCsvSeeder;
        $method = new ReflectionMethod($seeder, 'resolvePtId');
        $method->setAccessible(true);

        expect($method->invoke($seeder, ''))->toBeNull()
            ->and($method->invoke($seeder, '   '))->toBeNull()
            ->and($method->invoke($seeder, null))->toBeNull();
    });
});

<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->extend(Tests\TestCase::class)
    ->in('Feature/Schema');

describe('leave schema', function () {
    it('has canonical employee_profiles columns built from migrations', function () {
        expect(Schema::hasColumn('employee_profiles', 'kategori'))->toBeTrue()
            ->and(Schema::hasColumn('employee_profiles', 'tgl_bergabung'))->toBeTrue();
    });

    it('has hr_staff_can_approve_non_cuti column on users built from migrations', function () {
        expect(Schema::hasColumn('users', 'hr_staff_can_approve_non_cuti'))->toBeTrue();
    });

    it('redirects a stale mysql default connection to the disposable sqlite file', function () {
        $disposablePath = (new ReflectionClass($this))->getMethod('disposableSqlitePath')->invoke($this);

        // Simulate stale config cache / runtime default pointing to MySQL.
        // No MySQL connection is opened; we only mutate configuration and the
        // connection resolver state, then ask the harness to rebind safely.
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.database' => 'hrd_system']);
        DB::purge('mysql');
        DB::purge('sqlite');
        DB::setDefaultConnection('mysql');

        $this->rebindToDisposableSqlite();

        expect(DB::connection()->getDriverName())->toBe('sqlite')
            ->and(DB::connection()->getDatabaseName())->toBe($disposablePath)
            ->and(config('database.default'))->toBe('sqlite')
            ->and(array_key_exists('mysql', app('db')->getConnections()))->toBeFalse(
                'MySQL connection must not be resolved during sqlite harness rebind.'
            );
    });
});

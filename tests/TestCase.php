<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var array<class-string, string>
     */
    private static array $disposableDatabasePaths = [];

    /**
     * Build a disposable sqlite file from migrations before the test lifecycle
     * starts database transactions. This guarantees every test class runs on a
     * source-based schema without touching database/testing.sqlite or MySQL.
     */
    public function createApplication()
    {
        $this->guardTestDatabaseEnv();

        $app = parent::createApplication();

        // Artisan helpers need the application instance on the test case.
        $this->app = $app;

        $this->prepareDisposableSqlite();

        return $app;
    }

    /**
     * Ensure phpunit env promises an in-memory sqlite connection.
     *
     * This guards against obvious misconfiguration; runtime safety is enforced
     * separately by prepareDisposableSqlite() so stale config cache cannot
     * redirect the test back to MySQL after boot.
     */
    protected function guardTestDatabaseEnv(): void
    {
        $connection = env('DB_CONNECTION');

        if ($connection !== 'sqlite') {
            throw new \RuntimeException(
                'Tests must run on a sqlite database. Current DB_CONNECTION is ['
                .($connection ?: 'null').']. Refusing to run to avoid touching the active MySQL database.'
            );
        }

        $database = env('DB_DATABASE');

        if ($database !== ':memory:') {
            throw new \RuntimeException(
                'phpunit DB_DATABASE must be :memory: to avoid relying on a stale sqlite file. Current: ['
                .($database ?: 'null').'].'
            );
        }
    }

    /**
     * Public entry used by safety tests to prove the harness can recover from
     * a stale runtime default connection pointing to MySQL.
     */
    public function rebindToDisposableSqlite(): void
    {
        $this->prepareDisposableSqlite();
    }

    /**
     * Create a disposable sqlite file inside storage/framework from the
     * application migrations and point the sqlite connection to it.
     *
     * This method also forces the default database connection to sqlite so that
     * artisan commands and application code cannot accidentally use a cached
     * or stale default connection (e.g. mysql).
     */
    protected function prepareDisposableSqlite(): void
    {
        $path = $this->disposableSqlitePath();
        $needsMigrate = ! file_exists($path);

        if ($needsMigrate) {
            $dir = dirname($path);

            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            touch($path);
        }

        // Force the default connection to sqlite and point the sqlite
        // connection at the disposable file. This overrides any stale config
        // cache that may have left config('database.default') as mysql.
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => $path]);

        // Drop any previously resolved connections so the next lookup uses the
        // forced configuration. MySQL is purged defensively but never reconnected.
        DB::purge('mysql');
        DB::purge('sqlite');

        // Ensure every subsequent DB call uses the sqlite connection.
        DB::setDefaultConnection('sqlite');

        // Reconnect only the sqlite driver to the disposable file.
        DB::reconnect('sqlite');

        // Runtime safety assertion: the active default driver must be sqlite
        // and it must be connected to the exact disposable file.
        $this->assertRuntimeDatabaseIsSqlite($path);

        if ($needsMigrate) {
            $this->artisan('migrate', ['--force' => true]);
        }
    }

    /**
     * Runtime assertion that the default connection really is sqlite and points
     * at the expected disposable file. Throws before any schema operation if
     * something is misconfigured.
     */
    protected function assertRuntimeDatabaseIsSqlite(string $expectedPath): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver !== 'sqlite') {
            throw new \RuntimeException(
                'Test runtime default database driver is ['.$driver.'], expected sqlite. '
                .'Refusing to run migrations to avoid touching the active database.'
            );
        }

        $database = $connection->getDatabaseName();

        if ($database !== $expectedPath) {
            throw new \RuntimeException(
                'Test runtime sqlite database is ['.$database.'], expected ['.$expectedPath.']. '
                .'Refusing to run migrations to avoid touching the wrong database.'
            );
        }
    }

    protected function disposableSqlitePath(): string
    {
        $class = static::class;

        if (! isset(self::$disposableDatabasePaths[$class])) {
            $slug = preg_replace('/[^A-Za-z0-9_-]/', '_', $class);
            self::$disposableDatabasePaths[$class] = storage_path(
                'framework/testing-disposable-'.$slug.'-'.getmypid().'.sqlite'
            );
        }

        return self::$disposableDatabasePaths[$class];
    }

    public static function tearDownAfterClass(): void
    {
        $class = static::class;

        if (isset(self::$disposableDatabasePaths[$class])) {
            $path = self::$disposableDatabasePaths[$class];

            if (file_exists($path)) {
                @unlink($path);
            }

            unset(self::$disposableDatabasePaths[$class]);
        }

        parent::tearDownAfterClass();
    }
}

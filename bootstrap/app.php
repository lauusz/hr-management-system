<?php

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\HasSubordinates;
use App\Http\Middleware\PreventBrowserCache;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([__DIR__.'/../app/Console/Commands'])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
            'has.subordinates' => HasSubordinates::class,
            'prevent.cache' => PreventBrowserCache::class,
        ]);

        // Mencegah browser menyimpan cache halaman dinamis sehingga
        // pergantian user/login tidak menampilkan halaman lama.
        $middleware->web(append: [
            PreventBrowserCache::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

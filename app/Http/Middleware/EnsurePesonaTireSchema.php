<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsurePesonaTireSchema
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Schema::hasTable('inv_pesona_tires') || ! Schema::hasTable('inv_pesona_tire_histories') || ! Schema::hasTable('inv_pesona_vehicles')) {
            return response()->view('inventory.pesona.admin.tires.unavailable', [], 503);
        }

        return $next($request);
    }
}

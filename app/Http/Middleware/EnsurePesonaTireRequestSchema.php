<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsurePesonaTireRequestSchema
{
    public function handle(Request $request, Closure $next): Response
    {
        $ready = Schema::hasTable('inv_pesona_tire_requests') && Schema::hasTable('inv_pesona_tires')
            && Schema::hasTable('inv_pesona_tire_histories') && Schema::hasTable('inv_pesona_vehicles');

        return $ready ? $next($request) : response()->view('inventory.pesona.tire_requests.unavailable', [], 503);
    }
}

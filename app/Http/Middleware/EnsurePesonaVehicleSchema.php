<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsurePesonaVehicleSchema
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Schema::hasTable('inv_pesona_vehicles')
            && Schema::hasColumns('inv_pesona_requests', ['inv_pesona_vehicle_id', 'nopol_snapshot']),
            503, 'Fitur nopol Inventory Pesona belum siap. Hubungi administrator.');

        return $next($request);
    }
}

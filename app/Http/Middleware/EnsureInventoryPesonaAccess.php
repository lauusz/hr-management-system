<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureInventoryPesonaAccess
{
    public function handle(Request $request, Closure $next, ?string $level = null): Response
    {
        abort_unless($request->user() && Schema::hasTable('inv_pesona_access_users'), 403);

        $access = DB::table('inv_pesona_access_users')->where('user_id', $request->user()->id);

        if ($level === 'admin') {
            $access->where('is_admin', 1);
        }

        abort_unless($access->exists(), 403);

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOpsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->canManageOps(), 403, 'Akses Admin OPS tidak tersedia.');

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAtkMksAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->canManageAtkMks(), 403, 'Akses Admin ATK MKS tidak tersedia.');

        return $next($request);
    }
}

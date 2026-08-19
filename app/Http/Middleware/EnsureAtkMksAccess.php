<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAtkMksAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->canAccessAtkMks(), 403, 'Akses ATK MKS tidak tersedia.');

        return $next($request);
    }
}

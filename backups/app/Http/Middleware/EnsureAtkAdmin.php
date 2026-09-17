<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAtkAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->canManageAtk()) {
            abort(403, 'Anda tidak memiliki akses admin ATK.');
        }

        return $next($request);
    }
}

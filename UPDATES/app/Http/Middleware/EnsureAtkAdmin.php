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
            abort(403, 'Unauthorized ATK access.');
        }

        return $next($request);
    }
}

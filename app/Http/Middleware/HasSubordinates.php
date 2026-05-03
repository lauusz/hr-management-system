<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class HasSubordinates
{
    /**
     * Handle an incoming request.
     *
     * Izinkan akses jika user terdaftar sebagai direct_supervisor_id
     * atau manager_id dari minimal 1 user lain.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        // Cek apakah user ini adalah atasan (supervisor atau manager) dari siapa pun
        $hasSubordinates = User::where('direct_supervisor_id', $user->id)
            ->orWhere('manager_id', $user->id)
            ->exists();

        if (! $hasSubordinates) {
            abort(403, 'Anda tidak memiliki bawahan yang perlu di-approve.');
        }

        return $next($request);
    }
}

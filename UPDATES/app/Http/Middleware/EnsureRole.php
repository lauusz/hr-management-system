<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole; // Pastikan import Enum

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        // KONVERSI: Ambil value string dari Enum jika user->role adalah object Enum
        $userRole = $user->role instanceof UserRole 
            ? $user->role->value 
            : (string) $user->role;

        // Cek apakah role user ada di dalam daftar role yang diizinkan route
        // (Sekarang kita membandingkan String vs String, jadi aman)
        if (! in_array($userRole, $roles)) {
            abort(403, 'Unauthorized role.');
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLoanRequestEligibility
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            $request->user()?->isEligibleForEmployeeLoan(),
            403,
            'Pengajuan hutang hanya tersedia untuk karyawan dengan masa kerja minimal 1 tahun.'
        );

        return $next($request);
    }
}

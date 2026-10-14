<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClinicNotSuspended
{
    /**
     * Tokens are revoked when a doctor is suspended, this is the backstop for
     * any request that still reaches the API on behalf of a suspended clinic.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(
            $request->user()?->isClinicSuspended(),
            403,
            'This clinic account is suspended. Please contact support.'
        );

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnabled
{
    /**
     * Send users without two-factor authentication to the setup page.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('security.admin_requires_two_factor') && ! $request->user()->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('two-factor.show');
        }

        return $next($request);
    }
}

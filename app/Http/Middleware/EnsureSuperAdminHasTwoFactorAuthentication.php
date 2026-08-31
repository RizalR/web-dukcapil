<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdminHasTwoFactorAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isSuperAdmin() && ! $request->user()->hasConfirmedTwoFactorAuthentication()) {
            return redirect()->route('admin.two-factor.edit')
                ->with('status', 'Aktifkan autentikasi dua faktor sebelum mengakses panel Super Admin.');
        }

        return $next($request);
    }
}

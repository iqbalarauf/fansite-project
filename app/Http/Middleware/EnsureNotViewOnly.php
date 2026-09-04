<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotViewOnly
{
    /**
     * Prevent View-Only users from accessing restricted routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isViewOnly()) {
            abort(403, 'Akun View-Only tidak dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}

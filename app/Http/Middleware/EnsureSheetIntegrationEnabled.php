<?php

namespace App\Http\Middleware;

use App\Support\SettingBag;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSheetIntegrationEnabled
{
    /**
     * Reject the request unless the Sheet Integration feature is enabled.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(SettingBag::sheetIntegrationEnabled(), 404);

        return $next($request);
    }
}

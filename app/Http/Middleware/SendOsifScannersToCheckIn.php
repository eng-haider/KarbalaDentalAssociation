<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A hall scanner signs in through the OSIF dashboard's login but never sees
 * the dashboard itself (guests' names and phones): every page of it sends
 * them back to the scanning page. Signing out still works.
 */
class SendOsifScannersToCheckIn
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isOsifScanner() && ! $request->routeIs('filament.osif.auth.logout')) {
            return $request->expectsJson()
                ? abort(403)
                : redirect()->route('osif.checkin');
        }

        return $next($request);
    }
}

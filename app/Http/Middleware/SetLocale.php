<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Try getting locale from session
        $locale = Session::get('locale');

        // If not in session, try getting it from cookie
        if (! $locale) {
            $locale = $request->cookie('locale');
            if ($locale && \in_array($locale, ['id', 'en'])) {
                Session::put('locale', $locale);
            }
        }

        // Validate locale or fallback to default
        if (! $locale || ! \in_array($locale, ['id', 'en'])) {
            $locale = config('app.locale', 'id');
        }

        // Set application locale
        App::setLocale($locale);

        return $next($request);
    }
}

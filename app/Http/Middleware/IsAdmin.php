<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated and has the 'admin' role
        if ($request->user()) {
            if ($request->session()->get('is_kiosk_session')) {
                abort(403, 'Akses admin diblokir dalam mode kiosk.');
            }

            if ($request->user()->role === 'admin') {
                return $next($request);
            } elseif ($request->user()->role === 'user') {
                return redirect()->route('guest.home');
            }
        }

        return redirect()->route('login');
    }
}

<?php

use App\Http\Middleware\ArTokenAuth;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUser;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust the tunnel/reverse-proxy's X-Forwarded-* headers so Laravel knows the
        // original request was HTTPS — otherwise route()/url() emit http:// links that
        // get blocked by CSP as cross-origin against the https:// page actually loaded.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'ar.token' => ArTokenAuth::class,
            'admin' => IsAdmin::class,
            'user' => IsUser::class,
        ]);

        // Add SetLocale and SecurityHeaders middleware to web group
        $middleware->web(append: [
            SetLocale::class,
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

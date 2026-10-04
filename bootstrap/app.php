<?php

use App\Http\Middleware\AutoLoginForTrustedRequests;
use App\Http\Middleware\UseStaticAssetsForRemoteHost;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
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
        $middleware->trustProxies(at: '*');

        $middleware->prependToGroup('web', UseStaticAssetsForRemoteHost::class);

        // Opt-in owner auto-login (all off by default). The router sorts by the priority list, so it has to be
        // placed explicitly: after the session starts, before the 'auth' route middleware.
        $middleware->appendToGroup('web', AutoLoginForTrustedRequests::class);
        $middleware->prependToPriorityList(before: AuthenticatesRequests::class, prepend: AutoLoginForTrustedRequests::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

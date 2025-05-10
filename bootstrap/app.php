<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php', // <-- цей рядок має бути!
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add the middleware globally
        $middleware->append(\App\Http\Middleware\Cors::class);
        
        // If you want to use it as a named middleware, make sure it's registered properly
        $middleware->alias([
            'cors' => \App\Http\Middleware\Cors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Configure the exception handler
    })
    ->create();
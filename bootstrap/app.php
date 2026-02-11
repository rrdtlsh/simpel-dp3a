<?php

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
        // Tambahkan ini untuk mengatur redirect default jika user sudah login
        $middleware->redirectTo(
            guests: '/login',
            users: '/admin/dashboard' // Arahkan ke dashboard jika sudah login
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

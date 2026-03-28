<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // PINTU PINTAR: Redirect dinamis berdasarkan role
        $middleware->redirectTo(
            guests: '/login',
            users: function (Request $request) {
                // Gunakan $request->user() agar VS Code tidak memunculkan garis merah
                $user = $request->user();

                // Jika user ada dan dia adalah Admin, lempar ke dashboard admin
                if ($user && $user->role === 'admin') {
                    return route('admin.dashboard');
                }

                // Jika user biasa/bidang, arahkan ke daftar permintaan
                return route('user.permintaan');
            }
        );

        // ALIAS MIDDLEWARE
        $middleware->alias([
            'isAdmin' => \App\Http\Middleware\IsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

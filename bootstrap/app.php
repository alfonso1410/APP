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
    ->withMiddleware(function (Middleware $middleware): void {
        
        // --- INICIO DE LA CORRECCIÓN ---
        // Aquí registramos los "alias" para poder usarlos en las rutas.
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

            // --- ¡ESTA ES LA LÍNEA QUE NECESITABAS! ---
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
        // --- FIN DE LA CORRECCIÓN ---

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
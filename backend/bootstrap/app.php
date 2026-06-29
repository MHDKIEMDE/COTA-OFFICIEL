<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Inertia middleware for web routes
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            // Affiche « Bientôt disponible » sur tout le web (public + admin).
            // Réversible : retirer cette ligne pour réactiver le web. L'API n'est pas concernée.
            \App\Http\Middleware\WebComingSoon::class,
        ]);

        // Configuration CORS pour permettre les requêtes depuis Flutter Web
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // Détermine la langue (FR/EN) de la réponse API selon l'utilisateur
        // ou l'en-tête Accept-Language
        $middleware->api(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        // Configuration Rate Limiting strict pour production
        $middleware->throttleApi('600,1'); // 600 req/min en dev — réduire à 60 en prod

        // Middleware personnalisé pour le panel admin
        $middleware->alias([
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'admin'       => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e): void {
            if (app()->bound('sentry') && env('SENTRY_LARAVEL_DSN')) {
                \Sentry\Laravel\Integration::captureUnhandledException($e);
            }
        });
    })->create();

<?php

use App\Http\Middleware\ResetPostgresTenantContext;
use App\Http\Middleware\SetCurrentTenant;
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
        /*
        |------------------------------------------------------------------
        | Global middleware stack (виконується для КОЖНОГО запиту)
        |------------------------------------------------------------------
        */
        $middleware->append([
            // 1. визначаємо тенанта (до будь-яких контролерів)
            SetCurrentTenant::class,

            // … (інші глобальні з коробки, якщо будемо використовувати)

            // 2. скидаємо контекст у кінці request - має бути **останнім**
            ResetPostgresTenantContext::class,
        ]);

        /*
        | Якщо потрібні групи:
        |
        | $middleware->web(fn ($stack) => $stack
        |       ->prepend(SetCurrentTenant::class)
        |       ->push(ResetPostgresTenantContext::class)
        | );
        */
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

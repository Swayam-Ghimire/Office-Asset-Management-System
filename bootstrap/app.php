<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Support\Str;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
        $middleware->encryptCookies(except: ['_error']);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            flash_error($e->getMessage());
            if ($request->header('X-Inertia')) {
                return back();
            }

            // fallback for not -inertia requests
            return redirect()->route('home');
        });
        // $exceptions->render(function (MethodNotAllowedHttpException $e) {
        //     flash_error($e->getMessage());
        //     return redirect()->route('home');
        // });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            return redirect()->route('home')->cookie(
                '_error', 'Get method not supported', 0.01 // expires in 1 second
            );
        });

    })->create();

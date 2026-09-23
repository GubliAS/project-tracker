<?php

use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Middleware\EnsureWorkspaceAccess;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetCurrentWorkspace;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetCurrentWorkspace::class,
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'workspace' => EnsureWorkspaceAccess::class,
            'platform' => EnsurePlatformAdmin::class,
        ]);

        $middleware->redirectTo(
            guests: fn () => route('login'),
            users: fn () => route('dashboard'),
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (! $request->header('X-Inertia')) {
                return null;
            }

            return Inertia::render('Errors/Forbidden', [
                'title' => 'Forbidden',
                'message' => $exception->getMessage() ?: 'You do not have permission to do that.',
            ])->toResponse($request)->setStatusCode(403);
        });

        $exceptions->render(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() !== 403 || ! $request->header('X-Inertia')) {
                return null;
            }

            return Inertia::render('Errors/Forbidden', [
                'title' => 'Forbidden',
                'message' => $exception->getMessage() ?: 'You do not have permission to do that.',
            ])->toResponse($request)->setStatusCode(403);
        });
    })->create();

<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureInspectionUser;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'inspection-user' => EnsureInspectionUser::class,
            'admin' => EnsureAdmin::class,
            'active' => EnsureUserIsActive::class,
        ]);
        $middleware->redirectUsersTo(fn () => route('dashboard'));
        $middleware->prependToPriorityList(AuthenticatesRequests::class, EnsureUserIsActive::class);
        $middleware->prependToPriorityList(SubstituteBindings::class, EnsureUserHasRole::class);
        $middleware->appendToGroup('web', EnsureUserIsActive::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if ($exception->getStatusCode() !== 403) {
                return null;
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }

            $feature = 'ini';
            foreach (config('access.features', []) as $pattern => $label) {
                if (Str::is($pattern, $request->route()?->getName() ?? '')) {
                    $feature = $label;
                    break;
                }
            }

            return response()->view('errors.403', ['feature' => $feature], 403);
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

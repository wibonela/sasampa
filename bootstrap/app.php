<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\EnsureUserActive::class,
        ]);

        $middleware->alias([
            'company.approved' => \App\Http\Middleware\EnsureCompanyApproved::class,
            'platform.admin' => \App\Http\Middleware\PlatformAdmin::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        // Rate limiting for API routes
        $middleware->throttleApi('60,1'); // 60 requests per minute
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle 419 CSRF token errors gracefully
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired. Please refresh and try again.'], 419);
            }

            // Get the intended URL or fallback to login
            $previousUrl = url()->previous();
            $currentUrl = $request->url();

            // If previous URL is the same as current (no referrer), go to login
            if ($previousUrl === $currentUrl || empty($previousUrl)) {
                return redirect()->route('login')
                    ->withErrors(['csrf' => 'Your session has expired. Please try again.']);
            }

            // Redirect back with error message
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['csrf' => 'Your session has expired. Please try again.']);
        });
    })->create();

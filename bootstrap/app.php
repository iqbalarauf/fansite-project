<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('app:fetch-theater-shows')->weeklyOn(1, '18:00'); // Senin jam 18.00
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\ShareMenuPages::class,
            \App\Http\Middleware\SanitizeInertiaProps::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response) {
            if (! app()->environment(['local', 'testing']) && in_array($response->getStatusCode(), [400, 403, 404, 500])) {
                return inertia('Errors/Error' . $response->getStatusCode(), [
                    'message' => match ($response->getStatusCode()) {
                        400 => 'The request could not be understood by the server.',
                        403 => 'You do not have permission to access this resource.',
                        404 => 'The page or resource you are looking for does not exist.',
                        500 => 'Something went wrong on our end. Please try again later.',
                        default => 'An error occurred.'
                    }
                ])->toResponse(request());
            }

            return $response;
        });
    })->create();

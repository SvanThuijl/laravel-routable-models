<?php

namespace Svanthuijl\Routable;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Svanthuijl\Routable\Console\Commands\GenerateRoutes;

class RoutableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole())
        {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/routable-models.php' => config_path('routable-models.php'),
            ], 'routable-config');

            // Publish migrations
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'routable-migrations');

            // Load migrations
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            // Load commands
            $this->commands([
                GenerateRoutes::class,
            ]);
        }

        // Load config
        $this->mergeConfigFrom(__DIR__ . '/../config/routable-models.php', 'routable-models');

        // Define and bind patterns
        foreach (config('routable-models.methods') as $method)
        {
            $bindName = 'routable' . Str::ucfirst($method);

            // Apply the pattern
            if (config('routable-models.pattern'))
                Route::pattern($bindName, config('routable-models.pattern'));

            // Bind the method
            Route::bind($bindName, function ($value) use ($method) {
                return Models\Route::wherePath($value)
                    ->whereMethod($method)
                    ->first();
            });
        }

        // Load routes
        Route::middleware('web')
            ->group(__DIR__ . '/../routes/routable.php');
    }


}

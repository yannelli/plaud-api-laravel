<?php

namespace Yannelli\LaravelPlaud;

use Illuminate\Support\ServiceProvider;

class PlaudServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/plaud.php', 'plaud'
        );

        // Register PlaudClient as a singleton
        $this->app->singleton(PlaudClient::class, function ($app) {
            $accessToken = config('plaud.access_token');
            return new PlaudClient($accessToken);
        });

        // Register PlaudService as a singleton
        $this->app->singleton(PlaudService::class, function ($app) {
            return new PlaudService($app->make(PlaudClient::class));
        });

        // Register alias for the facade
        $this->app->alias(PlaudService::class, 'plaud');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/plaud.php' => config_path('plaud.php'),
            ], 'plaud-config');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            PlaudClient::class,
            PlaudService::class,
            'plaud',
        ];
    }
}

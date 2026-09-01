<?php

namespace Yannelli\LaravelPlaud;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Yannelli\LaravelPlaud\Support\Jwt;

class PlaudServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/plaud.php', 'plaud'
        );

        $this->app->singleton(PlaudClient::class, function ($app) {
            $client = new PlaudClient(
                accessToken: config('plaud.access_token'),
                baseUrl: config('plaud.base_url'),
                userToken: config('plaud.user_token'),
                refreshToken: config('plaud.refresh_token'),
                deviceId: config('plaud.device_id'),
            );

            $workspaceId = config('plaud.workspace_id');
            $userToken = $client->getUserToken() ?: $client->getAccessToken();

            if (is_string($workspaceId) && $workspaceId !== '' && is_string($userToken) && $userToken !== '' && ! Jwt::isWorkspaceToken($userToken)) {
                try {
                    $client->mintWorkspaceToken($workspaceId);
                } catch (\Throwable) {
                    // Boot-time mint is best-effort; callers can invoke useWorkspace() later.
                }
            }

            return $client;
        });

        $this->app->singleton(PlaudService::class, function ($app) {
            return new PlaudService($app->make(PlaudClient::class));
        });

        $this->app->alias(PlaudService::class, 'plaud');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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

<?php

declare(strict_types=1);

namespace zfhassaan\jazzcash\provider;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use zfhassaan\JazzCash\JazzCash;

/**
 * JazzCash Service Provider
 *
 * Registers the JazzCash package with Laravel.
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application Services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/config.php' => config_path('jazzcash.php'),
            ], 'jazzcash-config');
        }
    }

    /**
     * Register the application Services in Service Provider
     *
     * @return void
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'jazzcash');

        // Register the main class to use with the facade
        $this->app->singleton('jazzcash', function ($app) {
            return new JazzCash();
        });
    }
}

<?php

namespace zfhassaan\jazzcash\provider;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap the application Services.
     */
    public function boot()
    {
        if($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/config.php'=>config_path('JazzCash.php'),
            ],'config');
        }
    }

    /**
     * Register the application Services in Service Provider
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../../config/config.php','jazzcash');

        // Register the main class to use with the facade
        $this->app->singleton('jazzcash',function () {
            return new Jazzcash;
        });
    }
}

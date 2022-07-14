<?php

namespace Ifullgaz\GlobalUniqueId;

use Illuminate\Support\ServiceProvider;

class GlobalUniqueIdServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/globaluniqueid.php' => config_path('globaluniqueid.php')],
                'globaluniqueid-config');
        }
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/globaluniqueid.php', 'globaluniqueid');
    }
}

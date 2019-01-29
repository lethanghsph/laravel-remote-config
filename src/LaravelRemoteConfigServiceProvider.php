<?php

namespace Lethanghsph\LaravelRemoteConfig;

use Illuminate\Support\ServiceProvider;

class LaravelRemoteConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/Config/laravel-remote-config.php' => config_path('laravel-remote-config.php'),]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (file_exists(__DIR__ . '/Helpers/Helper.php')) {
            require_once(__DIR__ . '/Helpers/Helper.php');
        }

        $this->mergeConfigFrom(__DIR__ . '/Config/laravel-remote-config.php', 'laravel-remote-config');
    }
}

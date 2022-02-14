<?php

namespace Sihq\LaravelReactive;

use Illuminate\Support\ServiceProvider;

class LaravelReactiveServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-reactive');
    
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
  
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-reactive.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-reactive');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-reactive', function () {
            return new LaravelReactive;
        });
    }
}

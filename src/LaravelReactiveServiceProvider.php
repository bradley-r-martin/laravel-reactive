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
    
        $this->loadViewsFrom(__DIR__.'/../publishables/resources/views', 'laravel-reactive');
        
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        if(env('REACTIVE')){
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }

        $this->loadRoutesFrom(__DIR__.'/reactive-route.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-reactive.php'),
            ], 'config');

            // Publishing the views.
            $this->publishes([
                __DIR__.'/../publishables/resources/views' => resource_path('views/vendor/laravel-reactive'),
            ], 'views');
            $this->publishes([
                __DIR__.'/../publishables' => base_path(),
            ], 'framework');

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-reactive'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-reactive'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
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

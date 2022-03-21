<?php

namespace Sihq;

use Illuminate\Support\ServiceProvider;

class SihqServiceProvider extends ServiceProvider
{
    public function boot()
    {

        $this->loadRoutesFrom(__DIR__.'/routes.php');
        // $this->loadMigrationsFrom(__DIR__.'/Migrations');
    }


    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/Config/Files.php', 'sihq');

    }

}

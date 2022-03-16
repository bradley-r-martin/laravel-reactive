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
}

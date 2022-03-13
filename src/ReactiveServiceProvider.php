<?php

namespace Sihq\Reactive;

use Illuminate\Support\ServiceProvider;

class ReactiveServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/Migrations');
    }
}

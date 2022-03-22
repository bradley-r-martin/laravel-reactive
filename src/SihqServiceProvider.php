<?php

namespace Sihq;

use Illuminate\Support\ServiceProvider;

use Sihq\Rules\PhoneRule;
use Sihq\Rules\AddressRule;


class SihqServiceProvider extends ServiceProvider
{
    public function boot()
    {

        $this->loadViewsFrom(__DIR__.'/Views', 'sihq');

        if(!env('reactive')){
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
        // $this->loadMigrationsFrom(__DIR__.'/Migrations');

        \Validator::extend("phone", PhoneRule::class);
        \Validator::extend("address", AddressRule::class);
    }


    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/Config/Files.php', 'sihq');

    }

}

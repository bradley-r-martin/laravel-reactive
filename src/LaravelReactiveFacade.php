<?php

namespace Sihq\LaravelReactive;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Sihq\LaravelReactive\Skeleton\SkeletonClass
 */
class LaravelReactiveFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-reactive';
    }
}

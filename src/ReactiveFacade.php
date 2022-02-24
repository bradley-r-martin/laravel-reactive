<?php

namespace Sihq\Reactive;

use Illuminate\Support\Facades\Facade;

class ReactiveFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'reactive';
    }
}

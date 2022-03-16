<?php

namespace Sihq;

use Illuminate\Support\Facades\Facade;

class SihqFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sihq';
    }
}

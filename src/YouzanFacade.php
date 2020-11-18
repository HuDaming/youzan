<?php

namespace Hudm\Youzan;

use Illuminate\Support\Facades\Facade;

class YouzanFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Youzan::class;
    }
}
<?php

namespace Michaelgatuma\Kopokopo\Facades;

use Illuminate\Support\Facades\Facade;

class Kopokopo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'Kopokopo';
    }
}

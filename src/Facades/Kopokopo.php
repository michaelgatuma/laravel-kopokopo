<?php

namespace Michaelgatuma\Kopokopo\Facades;

use Illuminate\Support\Facades\Facade;

class Kopokopo extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'kopokopo';
    }
}

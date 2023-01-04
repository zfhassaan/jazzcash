<?php

namespace Zfhassaan\JazzCash\Facade;

use Illuminate\Support\Facades\Facade;

class JazzcashFacade extends Facade
{
    /**
     * Get the registered name of the component
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'jazzcash';
    }
}

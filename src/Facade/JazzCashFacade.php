<?php

namespace zfhassaan\jazzcash;

use Illuminate\Support\Facades\Facade;

class JazzCashFacade extends Facade
{
    /**
     * Get the registered name of the component
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'jazzcash';
    }
}

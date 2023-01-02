<?php

namespace zfhassaan\jazzcash\facade;

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

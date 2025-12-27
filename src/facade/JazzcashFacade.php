<?php

declare(strict_types=1);

namespace zfhassaan\JazzCash\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * JazzCash Facade
 *
 * @method static \zfhassaan\JazzCash\JazzCash setAmount(float|int|string $amount)
 * @method static \zfhassaan\JazzCash\JazzCash setBillReference(string $billref)
 * @method static \zfhassaan\JazzCash\JazzCash setProductDescription(string $description)
 * @method static \Illuminate\Http\Response sendRequest()
 *
 * @see \zfhassaan\JazzCash\JazzCash
 */
class JazzcashFacade extends Facade
{
    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'jazzcash';
    }
}

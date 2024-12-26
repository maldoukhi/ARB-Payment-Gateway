<?php

namespace Tahdir\Arb\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Tahdir\Arb\Arb
 *
 * @see \Tahdir\Arb\Arb
 */
class Arb extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Tahdir\Arb\Arb::class;
    }
}

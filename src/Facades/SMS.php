<?php

namespace Cccdz\Sms\Facades;

use Illuminate\Support\Facades\Facade;

class SMS extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'SMS';
    }
}
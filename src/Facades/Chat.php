<?php

namespace PhpJunior\LaravelVideoChat\Facades;

use Illuminate\Support\Facades\Facade;

class Chat extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'chat';
    }
}

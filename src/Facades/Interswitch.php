<?php

namespace DevsNG\Interswitch\Facades;

use Illuminate\Support\Facades\Facade;

class Interswitch extends Facade
{
    /**
     * Get the binding in the IoC container.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'interswitch'; // IoC binding.
    }
}

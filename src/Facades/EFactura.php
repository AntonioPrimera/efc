<?php

namespace AntonioPrimera\Efc\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AntonioPrimera\Efc\EFactura
 */
class EFactura extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \AntonioPrimera\Efc\EFactura::class;
    }
}

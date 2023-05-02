<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static getNavLinks(int $currentPage, int $lastPage)
 */
class Nav extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'Nav';
    }
}

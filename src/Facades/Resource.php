<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 02-02-19
 * Time: 12:22
 */

namespace Gruter\ResourceViewer\Facades;


use Illuminate\Support\Facades\Facade;

class Resource extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'resources';
    }

}
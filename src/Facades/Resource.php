<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 02-02-19
 * Time: 12:22
 */

namespace Gruter\ResourceViewer\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * Class Resource
 * @package Gruter\ResourceViewer\Contracts
 * @method static register(mixed $resource)
 * @method static string get(String $name)
 * @method static string fromUri(String $uri)
 * @method static string[] all
 * @method static string[] allUris
 * @method static \Gruter\ResourceViewer\Resource find(string $name)
 * @method static \Gruter\ResourceViewer\Resource findOrFail(string $name)
 * @method static \Gruter\ResourceViewer\Resource findOrFailFromUri(string $uri)
 */
class Resource extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'resource';
    }

}
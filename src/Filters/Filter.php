<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 05-02-19
 * Time: 16:42
 */
namespace Gruter\ResourceViewer\Filters;

use Gruter\ResourceViewer\Element;
use Illuminate\Support\Str;

abstract class Filter extends Element
{

    public $value;

    public abstract function apply($query, $value);

    public abstract function options();

}
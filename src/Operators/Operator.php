<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 07-02-19
 * Time: 14:58
 */

namespace Gruter\ResourceViewer\Operators;

abstract class Operator
{

    public abstract function value();

    public abstract function apply($query, $field, $value);
}
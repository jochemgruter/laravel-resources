<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 05-02-19
 * Time: 16:46
 */

namespace Gruter\ResourceViewer;


use Illuminate\Support\Str;

abstract class BooleanFilter extends Filter
{

    private $attribute;

    private $label;

    public function apply($query, $value)
    {

    }
}
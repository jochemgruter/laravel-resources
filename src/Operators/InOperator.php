<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 07-02-19
 * Time: 15:08
 */

namespace Gruter\ResourceViewer\Operators;


class InOperator extends Operator
{

    public function value()
    {
        return 'IN (...)';
    }

    public function apply($query, $field, $value)
    {
        $query->whereIn($field, explode(',', $value));
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 07-02-19
 * Time: 14:59
 */

namespace Gruter\ResourceViewer\Operators;


class SimpleOperator extends Operator
{

    private $operator;

    public function __construct($operator)
    {
        $this->operator = $operator;
    }

    public function value()
    {
        return  $this->operator;
    }

    public function apply($query, $field, $value)
    {
        $query->where($field, $this->operator, $value);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 07-02-19
 * Time: 15:13
 */

namespace Gruter\ResourceViewer\Operators;


class LikePercentsOperator
{
    public function value()
    {
        return 'LIKE %...%';
    }

    public function apply($query, $field, $value)
    {
        $query->where($field, 'like', '%'.$value.'%');
    }

}
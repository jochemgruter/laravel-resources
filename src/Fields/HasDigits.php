<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 07-02-19
 * Time: 14:57
 */

namespace Gruter\ResourceViewer\Fields;


use Gruter\ResourceViewer\Operators\InOperator;
use Gruter\ResourceViewer\Operators\SimpleOperator;

trait HasDigits
{

    public function advancedSearchOperators(){
        return [
            new SimpleOperator('='),
            new SimpleOperator('>'),
            new SimpleOperator('>='),
            new SimpleOperator('<'),
            new SimpleOperator('<='),
            new SimpleOperator('!='),
            new InOperator()
        ];
    }
}
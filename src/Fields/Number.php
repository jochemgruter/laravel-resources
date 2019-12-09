<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 03-02-19
 * Time: 16:40
 */

namespace Gruter\ResourceViewer\Fields;


class Number extends Field
{


    public function view($attribute, $value)
    {
        return view('ResourceViewer::fields.text', ['field' => $this, 'attribute' => $attribute, 'value' => $value]);
    }
}
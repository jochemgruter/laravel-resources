<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 07-02-19
 * Time: 15:30
 */

namespace Gruter\ResourceViewer\Fields;


class ID extends Field
{

    use HasDigits;

    public function __construct($attribute = 'id', $label = 'ID')
    {
        parent::__construct($attribute, $label);
    }


    public function view($attribute, $value)
    {
        return view('ResourceViewer::fields.text', ['field' => $this, 'attribute' => $attribute, 'value' => $value]);
    }
}
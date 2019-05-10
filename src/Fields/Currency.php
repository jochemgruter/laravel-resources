<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 03-02-19
 * Time: 17:30
 */

namespace Gruter\ResourceViewer\Fields;


use Illuminate\Database\Eloquent\Model;

class Currency extends Field
{

    use HasDigits;

    protected function resolve(Model $model){
        return '€ '.$model->{$this->attribute};
    }

    public function view($attribute, $value)
    {
        return view('ResourceViewer::fields.text', ['field' => $this, 'attribute' => $attribute, 'value' => $value]);
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 07-03-19
 * Time: 11:56
 */

namespace Gruter\ResourceViewer\Fields;


use Gruter\ResourceViewer\Operators\BooleanOperator;
use Illuminate\Database\Eloquent\Model;

class Boolean extends Field
{

    public $yesValue = 'Yes';

    public $noValue = 'No';

    public $defaultEmpty = false;

    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label);
    }


    protected function resolve(Model $model)
    {
        $value = $model->getAttribute($this->attribute);

        return $value == 1 ? $this->yesValue : $this->noValue;
    }

    public function view($attribute, $value)
    {
        $options = [$this->noValue, $this->yesValue];

        if ($this->defaultEmpty) {
            if ($this->advancedSearchValue == null)
                $value = -1;
            $options = [-1 => '', 0 => $this->noValue, 1 => $this->yesValue];
        }

        return view('ResourceViewer::fields.selectbox', ['field' => $this, 'attribute' => $attribute, 'value' => $value, 'options' => $options]);
    }

    public function advancedSearchOperators()
    {
        return [];
    }


}
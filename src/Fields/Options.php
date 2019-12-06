<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 07-03-19
 * Time: 13:33
 */

namespace Gruter\ResourceViewer\Fields;


use Gruter\ResourceViewer\Operators\SimpleOperator;
use Illuminate\Database\Eloquent\Model;

class Options extends Field
{

    public $options = [];

    public $hideEmptyValue = false;

    public $defaultEmpty = false;

    public function __construct($attribute, $label = null, $options = [])
    {
        $this->options = $options;

        parent::__construct($attribute, $label);
    }

    protected function resolve(Model $model)
    {
        $value =  parent::resolve($model);

        if (isset($this->options[$value]))
            return $this->options[$value];

        return '';
    }


    public function hideEmptyValue(){
        $this->hideEmptyValue = true;
        return $this;
    }

    protected function view($attribute, $value)
    {
        $options = $this->options;

        if (!$this->hideEmptyValue && !$this->defaultEmpty)
            $options = array_prepend($options, '');

        if ($this->defaultEmpty) {
            if ($this->advancedSearchValue == null)
                $value = -1;
            $options = [-1 => ''] + $options;
        }

        return view('ResourceViewer::fields.selectbox', ['field' => $this, 'attribute' => $attribute, 'value' => $value, 'options' => $options]);
    }

    public function advancedSearchOperators()
    {
        return [
            new SimpleOperator('='),
            new SimpleOperator('!='),
        ];
    }
}
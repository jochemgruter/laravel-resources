<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 21-02-19
 * Time: 12:25
 */

namespace Gruter\ResourceViewer;


use Gruter\ResourceViewer\Fields\Boolean;
use Gruter\ResourceViewer\Fields\Options;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class FormBuilder
{

    private $fields;

    public $mode = 0;

    public $model;

    public $method = 'POST';

    public $action;

    public $actionButton;

    public function __construct(array $fields, $action, $mode = 0)
    {
        $this->fields = $fields;
        $this->action = $action;
        $this->mode = $mode;
        if ($mode == Resource::MODE_CREATE){
            $this->actionButton = 'Create';
        }
        if ($mode == Resource::MODE_UPDATE){
            $this->actionButton = 'Update';
        }
    }

    public function render(){
        $old = old();

        if (count($old) > 0){
            foreach($this->fields as $field){
                $field->value = $old[$field->attribute()];
            }
        }elseif ($this->model != null){
            foreach($this->fields as $field){
                $field->value = $this->model->getAttribute($field->attribute());
            }
        }

        return view('ResourceViewer::partials.form', ['form' => $this]);
    }

    public function getValidator(Request $request){
        // TODO rules type (eg update or creation)

        $data = $request->all();
        $rules = [];
        foreach($this->fields as $field){
            $rules[$field->attribute()] = $field->getRules();
        }
        return \Validator::make($data,  $rules);
    }

    public function validate(Request $request){

    }

    public function model(Model $model){
        $this->model = $model;
        return $this;
    }

    public function defaultEmpty(){
        foreach ($this->fields as $field) {
            if ($field instanceof Boolean || $field instanceof Options)
                $field->defaultEmpty = true;
        }
    }

    public function getFields(){
        return $this->fields;
    }

}
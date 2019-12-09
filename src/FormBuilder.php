<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 21-02-19
 * Time: 12:25
 */

namespace Gruter\ResourceViewer;


use Gruter\ResourceViewer\Contracts\Listable;
use Gruter\ResourceViewer\Fields\Boolean;
use Gruter\ResourceViewer\Fields\Field;
use Gruter\ResourceViewer\Fields\Options;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FormBuilder
{

    /**
     * The fields of the form
     *
     * @var Collection
     */
    private $fields;

    /**
     * The form mode for creation and update forms (1 = update, 2 = create)
     *
     * @var int
     */
    public $mode = 0;

    /**
     * Model of the form
     *
     * @var Model
     */
    public $model;

    /**
     * The form action method
     *
     * @var string
     */
    public $method = 'POST';

    /**
     * The action url
     *
     * @var string
     */
    public $action;

    /**
     * A label for the apply button
     *
     * @var string
     */
    public $actionButton;

    /**
     * Amount of CSS bootstrap columns for the labels column
     *
     * @var int
     */
    public $columnsLabel = 3;

    /**
     * Initiates a new FormBuilder with a fields array
     *
     * @param  Collection  $fields
     * @param  string $action
     * @param  int  $mode
     */
    public function __construct(Collection $fields, $action, $mode = 0)
    {
        $this->fields = $fields->reject(function($field){
            return $field instanceof Listable;
        });

        $this->action = $action;
        $this->mode = $mode;
    }

    /**
     * Create a view of the form
     *
     * @param  bool  $fieldsOnly  if <true> the fields will not be rendered in a form tag.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render($fieldsOnly = false){
        $old = old() ?? [];

        foreach($this->fields as $field){
            if(key_exists($field->attribute(), $old))
                $field->value = $old[$field->attribute()];

            elseif(! is_null($this->model))
                $field->value = $this->model->getAttribute($field->attribute());

            if(! is_null($this->model))
                $field->model = $this->model;
        }

        if ($fieldsOnly)
            return view('ResourceViewer::partials.formFields', ['form' => $this]);

        return view('ResourceViewer::partials.form', ['form' => $this]);
    }

    /**
     * Get the validator that is initiated with the field rules and request input
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function getValidator(Request $request){
        $rules = $this->fields->mapWithKeys(function (Field $field) {
            return [$field->attribute => $field->getRules($this->mode)];
        })->toArray();

        return \Validator::make($request->all(),  $rules);
    }

    /**
     * Validate the validator.
     *
     * @param  Request  $request
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(Request $request){
        return $this->getValidator($request)->validate();
    }

    /**
     * Set a model for the form. All model values will be parsed in the fields.
     *
     * @param  Model  $model
     * @return $this
     */
    public function model(Model $model){
        $this->model = $model;
        return $this;
    }

    /**
     * Set the default values of the fields empty. Used in creation, filters, and search forms.
     *
     * @return $this
     */
    public function defaultEmpty(){
        foreach ($this->fields as $field) {
            if ($field instanceof Boolean || $field instanceof Options)
                $field->defaultEmpty = true;
        }
        return $this;
    }

    /**
     * Get the fields of the form.
     *
     * @return Collection
     */
    public function getFields(){
        return $this->fields;
    }

}
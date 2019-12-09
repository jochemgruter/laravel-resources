<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 03-02-19
 * Time: 16:31
 */

namespace Gruter\ResourceViewer\Fields;


use Closure;
use Gruter\ResourceViewer\Element;
use Gruter\ResourceViewer\Operators\LikePercentsOperator;
use Gruter\ResourceViewer\Operators\SimpleOperator;
use Gruter\ResourceViewer\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class Field extends Element
{

    public $attribute;

    protected $label;

    public $table = null;

    public $value;

    public $model;

    public $help;

    public $displayMode = false;

    public $showOnIndex = true;
    public $showOnDetail = true;
    public $showOnUpdate = true;
    public $showOnCreate = true;
    public $showOnLookup = true;

    public $displayOnlyOnUpdate = false;

    public $showCallbacks = [];

    public $sortable = false;

    public $searchable = false;

    public $searchableAdvanced = false;

    public $advancedSearchValue;
    public $advancedSearchOperator;

    /**
     * @var Resource
     */
    public $resource;

    private $displayCallback;

    private $rules = [];
    private $rulesCreation = [];
    private $rulesUpdate = [];

    public function __construct($attribute, $label = null){
        $this->attribute = $attribute;

        $this->label = $label ?? Str::title(Str::snake($attribute, ' '));
    }

    public static function  make(...$args){
        return new static(...$args);
    }


    /**
     * @param $attribute
     * @param $value
     * @return \View returns the view of the editable mode of the field.
     */
    protected abstract function view($attribute, $value);

    /**
     * @param null $attribute
     * @param null $value
     * @return \View Renders the view of the editable mode of the field
     */
    public function render($attribute = null, $value = null){
        if (is_null($attribute)) $attribute = $this->attribute;
        if (is_null($value)) $value = $this->value;

        return $this->view($attribute, $value);
    }

    protected function resolve(Model $model){
        return $model->getAttribute($this->attribute);
    }

    public function displayUsing(callable $displayUsing){
        $this->displayCallback = $displayUsing;
        return $this;
    }

    public function display(Model $model){
        $value = $this->resolve($model);

        if (is_callable($this->displayCallback))
            $value = call_user_func($this->displayCallback, $value, $model);

        //TODO html escaping
        return $value;
    }

    public function attribute($attribute = null){
        if (func_num_args() == 0)
            return $this->attribute;

        $this->attribute = $attribute;
        return $this;
    }

    public function value($value = null){
        if (func_num_args() == 0)
            return $this->value;

        $this->value = $value;
        return $this;
    }

    public function label(){
        return $this->label;
    }

    public function help($help){
        $this->help = $help;
        return $this;
    }

    public function searchable(){
        $this->searchable = true;
        return $this;
    }

    public function advancedSearchable(){
        $this->searchableAdvanced = true;
        return $this;
    }

    public function advancedSearchOperators(){
        return [
            new SimpleOperator('='),
            new SimpleOperator('!='),
            new SimpleOperator('LIKE'),
            new LikePercentsOperator(),
            new SimpleOperator('NOT LIKE'),
        ];
    }

    public function sortable(){
        $this->sortable = true;
        return $this;
    }

    public function sortUrl(){
        $query = \Request::all();

        $keyAttribute = 'sort_'.$this->resource->name();
        $keyDirection = 'sort_'.$this->resource->name().'_direction';

        $query[$keyAttribute] = $this->attribute;

        if (isset($query[$keyDirection]) && $query[$keyDirection] == 'asc')
            $query[$keyDirection] = 'desc';
        else
            $query[$keyDirection] = 'asc';

        return '?'.http_build_query($query);
    }

    public function useForLookup(){
        $this->showOnLookup = true;
        return $this;
    }

    public function hideOnIndex(){
        $this->showOnIndex = false;
        return $this;
    }

    public function hideOnDetail(){
        $this->showOnDetail = false;
        return $this;
    }

    public function hideOnUpdate(){
        $this->showOnUpdate = false;
        return $this;
    }

    public function hideOnCreate(){
        $this->showOnCreate = false;
        return $this;
    }

    public function onlyOnIndex(){
        $this->hideOnUpdate()->hideOnCreate()->hideOnDetail();
        $this->showOnIndex = true;
        return $this;
    }

    public function onlyOnDetail(){
        $this->hideOnindex()->hideOnCreate()->hideOnUpdate();
        $this->showOnDetail = true;
        return $this;
    }

    public function onlyOnUpdate(){
        $this->hideOnindex()->hideOnCreate()->hideOnDetail();
        $this->showOnUpdate = true;
        return $this;
    }

    public function onlyOnCreate(){
        $this->hideOnindex()->hideOnUpdate()->hideOnDetail();
        $this->showOnCreate = true;
        return $this;
    }

    public function showOnDetail(Closure $callback){
        $this->showOnDetail = true;
        $this->showCallbacks['showOnDetail'] = $callback;
        return $this;
    }

    public function showOnUpdate(Closure $callback){
        $this->showOnUpdate = true;
        $this->showCallbacks['showOnUpdate'] = $callback;
        return $this;
    }

    public function showOnIndex(Closure $callback){
        $this->showOnIndex = true;
        $this->showCallbacks['showOnIndex'] = $callback;
        return $this;
    }

    public function showOnCreate(Closure $callback){
        $this->showOnIndex = true;
        $this->showCallbacks['showOnCreate'] = $callback;
        return $this;
    }

    public function showUsingCallback($criteria, $model = null){
        if (!isset($this->showCallbacks[$criteria]))
            return true;

        return call_user_func($this->showCallbacks[$criteria], $model);
    }

    public function hideOnForms(){
        $this->hideOnUpdate()->hideOnCreate();
        return $this;
    }

    public function displayOnlyOnUpdate(){
        $this->displayOnlyOnUpdate = true;
        return $this;
    }

    public function setResource(Resource $resource){
        $this->resource = $resource;
    }

    public function table($table){
        $this->table = $table;
        return $this;
    }

    public function rules($rules){
        $this->rules = is_string($rules) ? func_get_args() : $rules;
        return $this;
    }

    public function creationRules($rules){
        $this->rulesCreation = is_string($rules) ? func_get_args() : $rules;
        return $this;
    }

    public function updateRules($rules){
        $this->rulesUpdate = is_string($rules) ? func_get_args() : $rules;
        return $this;
    }

    public function getRules($mode = 0){
        $rules = $this->rules;

        if ($mode == Resource::MODE_CREATE){
            $rules = array_merge($rules, $this->rulesCreation);
        }
        if ($mode == Resource::MODE_UPDATE){
            $rules = array_merge($rules, $this->rulesUpdate);
        }
        return $rules;
    }


}
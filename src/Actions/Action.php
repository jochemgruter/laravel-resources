<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 07-02-19
 * Time: 15:54
 */

namespace Gruter\ResourceViewer\Actions;

use Gruter\ResourceViewer\Element;
use Gruter\ResourceViewer\FormBuilder;
use Gruter\ResourceViewer\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class Action extends Element
{

    public $icon = 'fa-play';

    public $confirmation = true;

    public $async = true;

    public $multiSelection = true;

    public $displayOnRow = false;

    public $displayOnRowWithLabel = false;

    private $resource;

    private $fields = null;

    /**
     * Permission to run the action
     *
     * @var callable|bool
     */
    private $runCallback = true;

    public abstract function handle(Request $request, Collection $models);

    protected abstract function fields();

    public function getFields(){
        if ($this->fields == null){
            $this->fields = collect($this->fields() ?? []);
        }
        return $this->fields;
    }

    public function hasForm(){
        return count($this->getFields()) > 0;
    }

    public function makeForm(){
        if($this->resource == null)
            return null; //TODO throw exception

        $actionUrl = route('resources.action.form_submit', ['resource' => $this->resource->uri(), 'action' => $this->name()])
            . '?' . request()->getQueryString();

        $fields = $this->getFields();
        $form = new FormBuilder($fields, $actionUrl);
        $form->defaultEmpty();
        $form->columnsLabel = 4;
        return $form;
    }

    public function setResource(Resource $resource){
        $this->resource = $resource;
    }


    public function singleOnly(){
        $this->multiSelection = false;
        $this->displayOnRow = true;
        return $this;
    }

    public function displayOnRow($displayOnRowWithLabel = false){
        $this->displayOnRow = true;
        $this->displayOnRowWithLabel = $displayOnRowWithLabel;
        return $this;
    }

    public function icon($icon){
        $this->icon = $icon;
        return $this;
    }

    /**
     * Set the permission to run the Action
     *
     * @param  callable|bool $callback
     * @return $this
     */
    public function canRun($callback){
        $this->runCallback = $callback;
        return $this;
    }

    /**
     * Authorisation to run the Action
     *
     * @param  Model  $model
     * @return bool|callable|mixed
     */
    public function authorizedToRun(Model $model)
    {
        if (is_bool($this->runCallback))
            return $this->runCallback;

        if (is_callable($this->runCallback))
            return call_user_func($this->runCallback, $model);

        return true;
    }

}
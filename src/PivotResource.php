<?php


namespace Gruter\ResourceViewer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PivotResource extends Resource
{

    public $pivotKeyName = 'id';

    private $instance;

    public $foreignResource;
    public $relatedResource;
    public $pivotTable;

    public $foreignPivotKey;
    public $relatedPivotKey;
    public $parentKey;
    public $relatedKey;

    private $relationName;

    private $fields;
    private $filers;

    private $actions;

    private $uri;

    protected $canAssign = true;

    private $label;
    private $labelSingular;

    public function __construct(Model $model, $foreignResource, $relatedResource, $pivotTable,
                                $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName, $uri)
    {

        $this->instance = $model;
        $this->foreignResource = $foreignResource;
        $this->relatedResource = $relatedResource;
        $this->pivotTable = $pivotTable;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;
        $this->parentKey = $parentKey;
        $this->relatedKey = $relatedKey;
        $this->relationName = $relationName;
        $this->uri = $uri;
    }

    public function name()
    {
        return $this->uri;
    }

    public function label()
    {
        return $this->label;
    }

    public function labelSingular()
    {
        return $this->labelSingular;
    }

    public function setLabel($label, $labelSingular = null)
    {
        $this->label = $label;
        $this->labelSingular = $labelSingular ?? Str::singular($label);
    }

    public function fields()
    {
        return $this->fields;
    }

    public function filters()
    {
        return $this->filers;
    }

    public function actions()
    {
        return $this->actions;
    }

    public function makeAssignForm()
    {
        $fields = $this->getFields('showOnCreate');
        $form = new FormBuilder($fields, $this->route('assign'));
        return $form;
    }

    final public function canAssign($callback = true){
        $this->canAssign = $callback;
    }

    final public function authorizedToAssign()
    {
        if (is_bool($this->canAssign))
            return $this->canAssign;

        if (is_callable($this->canAssign))
            return call_user_func($this->canAssign);

        return true;
    }

    public function route($type, $id = 0)
    {
        if ($type == 'create')
            return route('resource.create', ['resource' => $this->relatedResource::uri()]);

        return route('resource.pivot.'.$type, [
            'resource' => $this->foreignResource::uri(),
            'id' => $this->instance->getKey(),
            'pivot' => $this->uri,
            'pivotId' => $id
        ]);
    }

    public function findModel($id)
    {
        $query = $this->newQuery()
            ->where($this->pivotTable.'.'.$this->pivotKeyName, $id);

        $model = $this->getQueryResult($query)
            ->first();

        return $model;
    }

    public function newQuery()
    {
        $query = $this->instance->belongsToMany($this->relatedResource::$model, $this->pivotTable, $this->foreignPivotKey,
            $this->relatedPivotKey, $this->parentKey, $this->relatedKey);

        $query->withPivot($this->pivotKeyName);

        foreach($this->getFields('showOnIndex') as $field){
            $query->withPivot($field->attribute);
        }

        return $query;

    }

    public function getQueryResult($query)
    {
        $result = $query->get()->map(function($item){
            $item->pivot->setRelation($this->relationName, $item);
            return $item->pivot;
        });

        return $result;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    public function setFilters($filters)
    {
        $this->filers = $filters;

        return $this;
    }

    public function setActions($actions)
    {
        $this->actions = $actions;

        return $this;
    }

}
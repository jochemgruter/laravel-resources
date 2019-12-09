<?php


namespace Gruter\ResourceViewer\Fields;

use Gruter\ResourceViewer\PivotResource;
use Gruter\ResourceViewer\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BelongsToMany extends Field implements Listable
{

    public $showOnIndex = false;

    public $uri;

    /**
     * @var Resource
     */
    private $relatedResource;

    private $pivotTable = null;
    private $foreignPivotKey = null;
    private $relatedPivotKey = null;
    private $parentKey = null;
    private $relatedKey = null;

    private $resourceClosure;

    private $fields;

    private $actions;

    public function __construct($resource, $label, $pivotTable, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null)
    {
        if (!(($this->relatedResource = new $resource) instanceof Resource)) {
            throw new \InvalidArgumentException('Resource parameters must be a class which is an instance of the Resource class');
        }
        $this->pivotTable = $pivotTable;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;
        $this->parentKey = $parentKey;
        $this->relatedKey = $relatedKey;
        $this->uri = $pivotTable;

        parent::__construct($resource, $label ?? $this->relatedResource->label());
    }

    public function display(Model $model)
    {
        $pivotResource = $this->makePivotResource($model);

        return $pivotResource->renderIndexTable();

    }

    public function tapResource(\Closure $resourceClosure){
        $this->resourceClosure = $resourceClosure;
        return $this;
    }

    public function withFields(array $fields){
        $this->fields = $fields;
        return $this;
    }

    public function withActions(array $actions){
        $this->actions = $actions;
        return $this;
    }

    public function uri($uri){
        $this->uri = $uri;
        return $this;
    }

    /**
     * @param Model $model the related model instance of the pivot
     * @return PivotResource
     */
    public function makePivotResource(Model $model){
        $parentKey = $this->parentKey ?? $model->getKeyName();
        $relatedKey = $this->relatedResource->newModel()->getKeyName();
        $foreignPivotKey = $this->foreignPivotKey ?? Str::singular($model->getTable()).'_'.$parentKey;
        $relatedPivotKey = $this->relatedPivotKey ?? Str::snake(Str::singular(class_basename($this->relatedResource))).'_'.$relatedKey;

        $relationName = str_replace('_'.$relatedKey, '', $relatedPivotKey);

        $fields = array_merge([
            BelongsTo::make($relatedPivotKey, $this->relatedResource->labelSingular(), $this->relatedResource,
                $this->relatedKey, $relationName)
                ->rules('required'),
        ], $this->fields ?? []);

        $resource = new PivotResource($model, $this->resource, $this->relatedResource, $this->pivotTable,
            $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName, $this->uri);

        $resource->setFields($fields);
        $resource->setActions($this->actions);
        $resource->setLabel($this->label);

        if ($this->resourceClosure != null)
            call_user_func($this->resourceClosure, $resource);

        return $resource;
    }

    protected function resolve(Model $model)
    {

    }

    protected function view($attribute, $value)
    {

    }
}
<?php


namespace Gruter\ResourceViewer\Fields;


use App\Models\Customer;
use App\Resources\Brands;
use Gruter\ResourceViewer\Contracts\Listable;
use Gruter\ResourceViewer\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BelongsToMany extends Field implements Listable
{

    // TODO for this field:
    //      make actions apply to the pivot table
    //      hide show and can see buttons

    public $showOnIndex = false;

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
            throw new \InvalidArgumentException('Resource parameters must be a class which is a child of the Resource class');
        }
        $this->pivotTable = $pivotTable;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;
        $this->parentKey = $parentKey;
        $this->relatedKey = $relatedKey;

        parent::__construct($resource, $label ?? $this->relatedResource->label());
    }

    public function display(Model $model)
    {
        $parentKey = $this->parentKey ?? $model->getKeyName();
        $relatedKey = $this->relatedResource->newModel()->getKeyName();
        $foreignPivotKey = $this->foreignPivotKey ?? Str::singular($model->getTable()).'_'.$parentKey;
        $relatedPivotKey = $this->relatedPivotKey ?? Str::snake(Str::singular(class_basename($this->relatedResource))).'_'.$relatedKey;

        $resource = $this->relatedResource
            ->relatedToPivot($model, $this->pivotTable, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $this->fields)
            ->setActions($this->actions);

        if ($this->resourceClosure != null)
            call_user_func($this->resourceClosure, $resource);

        return $resource->renderIndexTable();

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

    protected function resolve(Model $model)
    {

    }

    protected function view($attribute, $value)
    {

    }
}
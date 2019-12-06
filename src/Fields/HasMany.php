<?php


namespace Gruter\ResourceViewer\Fields;


use Gruter\ResourceViewer\Resource;
use Illuminate\Database\Eloquent\Model;

class HasMany extends Field implements Listable
{

    public $showOnIndex = false;

    /**
     * @var Resource
     */
    private $relatedResource;

    private $foreignKey = null;
    private $ownerKey = null;

    private $resourceClosure;

    public function __construct($resource, $label = null, $foreignKey = null, $ownerKey = null)
    {
        if (!(($this->relatedResource = new $resource) instanceof Resource)) {
            throw new \InvalidArgumentException('Resource parameters must be a class which is a child of the Resource class');
        }

        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;

        parent::__construct($resource, $label ?? $this->relatedResource->label());
    }

    public function display(Model $model)
    {
        $resource = $this->relatedResource
            ->related($model, $this->foreignKey, $this->ownerKey);

        if ($this->resourceClosure != null)
            call_user_func($this->resourceClosure, $resource);

        return $resource->renderIndexTable();

    }

    public function tapResource(\Closure $resourceClosure){
        $this->resourceClosure = $resourceClosure;
        return $this;
    }

    protected function resolve(Model $model)
    {

    }

    protected function view($attribute, $value)
    {

    }
}
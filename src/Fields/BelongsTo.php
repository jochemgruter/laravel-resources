<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 03-02-19
 * Time: 17:29
 */

namespace Gruter\ResourceViewer\Fields;


use App\Models\Customer;
use Gruter\ResourceViewer\Operators\SimpleOperator;
use Gruter\ResourceViewer\Resource;
use Gruter\ResourceViewer\Tests\Fixtures\Categories;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BelongsTo extends Field
{

    private $related;

    /**
     * @var \Gruter\ResourceViewer\Resource
     */
    private $relatedInstance;

    private $ownerKey;

    private $relationName;

    private $disableLink = false;

    public function __construct($attribute, $label = null, $related = null, $ownerKey = 'id', $relationName = null){

        $this->related = $related ?? $this->guessRelatedResource($attribute);

        $this->relatedInstance = new $this->related;

        $this->ownerKey = $ownerKey;

        if (is_null($relationName)){
            $relationName = camel_case(str_replace('_'.$ownerKey, '', $attribute));
        }

        $this->relationName = $relationName;

        if ( ! ($this->relatedInstance instanceof \Gruter\ResourceViewer\Resource))
        throw new \InvalidArgumentException('Related resource must be a child of Resource');

        $this->displayUsing(function($value, Model $model){
            if ($relatedModel = $this->getRelatedModel($model))
                return $this->getDisplayValue($relatedModel);

            return '';
        });

        parent::__construct($attribute, $label);
    }


    private function guessRelatedResource($attribute){
        $name = ucfirst(camel_case(str_replace('_id', '', Str::plural($attribute))));

        return Resource::get($name);
    }

    public function getRelatedModelInstance(){
        $model = ($this->related)::$model;
        return new $model;
    }

    public function getForeignTitle(){
        return ($this->related)::$title;
    }

    public function getDisplayValue(Model $relatedModel){
        $value = $relatedModel->getAttributeValue($this->getForeignTitle());

        if (! $this->disableLink && $this->relatedInstance->authorizedToView($relatedModel))
            return view('ResourceViewer::partials.link', [
                'url' => $this->getRelatedLink($relatedModel), 'label' => $value]);


        return $value ?? '';
    }

    public function getRelatedModel(Model $model){

        if (isset($model->{$this->relationName}) && $model->{$this->relationName} instanceof $this->related::$model)
            $relatedModel = $model->{$this->relationName};

        else
            $relatedModel = $model->belongsTo($this->related::$model, $this->attribute, $this->ownerKey,
                $this->relationName)->first();

        return $relatedModel;
    }

    public function getRelatedModelById($id){
        $model = ($this->related)::$model;
        return $model::where($this->ownerKey, $id)->first();
    }

    protected function view($attribute, $value, ...$args)
    {

        if (! is_null($this->model))
            $relatedModel = $this->getRelatedModel($this->model);

        else if (!is_null($value))
            $relatedModel = $this->getRelatedModelById($value);

        if (isset($relatedModel))
            $relatedValue = $this->getDisplayValue($relatedModel);

        $resourceUri = ($this->related)::uri();
        return view('ResourceViewer::fields.belongsTo',
            ['field' => $this, 'resourceUri' => $resourceUri, 'attribute' => $attribute, 'value' => $value,
                'relatedValue' => $relatedValue ?? '']);
    }

    public function advancedSearchOperators()
    {
        return [
            new SimpleOperator('='),
            new SimpleOperator('!='),
        ];
    }

    public function getRelatedLink(Model $relatedModel){
        return $this->relatedInstance->route('show', $relatedModel->getKey());
    }

    public function disabledLink(){
        $this->disableLink = true;
        return $this;
    }
}
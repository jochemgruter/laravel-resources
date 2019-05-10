<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 03-02-19
 * Time: 17:29
 */

namespace Gruter\ResourceViewer\Fields;


use Gruter\ResourceViewer\Facades\Resource;
use Gruter\ResourceViewer\Operators\SimpleOperator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BelongsTo extends Field
{

    // TODO remove the VOB model option if all VOB models have a Resource

    private $related;

    /**
     * Temporarily field
     * @var boolean true if the related field is a vob model
     */
    private $isVobModel;

    /**
     * BelongsTo constructor.
     * @param $attribute
     * @param null $label
     * @param null $related  either Resource or VOB model
     * @param boolean set true if there is no resource for the relation and the related class is a VOB model
     */
    public function __construct($attribute, $label = null, $related = null, $isVobModel = false){

        $this->related = $related ?? $this->guessRelatedResource($attribute);

        $this->isVobModel = $isVobModel;

        parent::__construct($attribute, $label);
    }

    protected function resolve(Model $model){

        // TODO  fix the _id and find the actual relation name
        $relationName = camel_case(str_replace('_id', '', $this->attribute));
        $title = null;

        $title = $this->getForeignTitle();
        $value = '';

        if ($model->{$relationName} != null)
            $value = $model->{$relationName}->{$title};

        return $value;

    }

    private function guessRelatedResource($attribute){
        $name = ucfirst(camel_case(str_replace('_id', '', Str::plural($attribute))));

        return Resource::get($name);
    }

    public function getRelatedModelInstance(){
        if ($this->isVobModel){
            return new $this->related;
        }else{
            $model = ($this->related)::$model;
            return new $model;
        }
    }

    public function getForeignTitle(){
        if ($this->isVobModel){
            return($this->related)::$default_value;
        }else{
            return ($this->related)::$title;
        }
    }

    public function getRelatedValue($value = null){
        if(is_null($value)) $value = $this->value;


        $related  = '';
        if (is_numeric($value) && $value > 0) {
            $title = $this->getForeignTitle();
            $model = ($this->related::$model)::select($title)->find($value);
            if ($model != null) {
                $related = $model->getAttribute($title);
            }
        }

        return $related;
    }

    protected function view($attribute, $value, ...$args)
    {
        $resourceUri = ($this->related)::uri();
        return view('ResourceViewer::fields.belongsTo', ['field' => $this, 'resourceUri' => $resourceUri, 'attribute' => $attribute, 'value' => $value]);
    }

    public function advancedSearchOperators()
    {
        return [
            new SimpleOperator('='),
            new SimpleOperator('!='),
        ];
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 26-02-19
 * Time: 16:20
 */

namespace Gruter\ResourceViewer\Actions;


use Gruter\ResourceViewer\Fields\Boolean;
use Gruter\ResourceViewer\Fields\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MassUpdate extends Action
{

    private $fields = [];

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }


    public function handle(Request $request, Collection $models)
    {
        $models->each(function($model) use ($request){

            foreach($this->fields as $field){
                $value = $request->get($field->attribute());

                if ($field instanceof Boolean || $field instanceof Options) {
                    if ($value == -1)
                        continue;

                }elseif(empty($value) || $value == null)
                    continue;

                $model->{$field->attribute()} = $value;
            }

            $model->save();

        });

        return true;
    }

    public function fields()
    {
        return $this->fields;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 07-02-19
 * Time: 15:54
 */

namespace Gruter\ResourceViewer\Actions;

use Gruter\ResourceViewer\Element;
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

    public abstract function handle(Request $request, Collection $models);

    public abstract function fields();

    public function canRun(Collection $models){
        return true;
    }

    /**
     * @param $models mixed either array, collection or single model instance
     * @return bool returns true if the action is authorized to run on the given models
     */
    final public function authorizedToRun($models)
    {
        if (is_array($models))
            $models = collect($models);

        if ($models instanceof Model)
            $models = collect([$models]);

        if ($models instanceof Collection)
            return $this->canRun($models);

        return false;
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

}
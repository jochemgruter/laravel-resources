<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 07-02-19
 * Time: 15:44
 */

namespace Gruter\ResourceViewer;


use Closure;
use Illuminate\Support\Str;

class Element
{

    private $seeCallback;


    public function name(){
        return strtolower(class_basename(get_called_class()));
    }

    public function label(){
        return Str::title(Str::snake(class_basename(get_called_class()), ' '));
    }

    public function canSee(Closure $callback)
    {
        $this->seeCallback = $callback;
        return $this;
    }

    public function authorizedToSee()
    {
        return $this->seeCallback ? call_user_func($this->seeCallback) : true;
    }

}
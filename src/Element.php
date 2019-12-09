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

    /**
     * Permission of the element
     *
     * @var Closure|bool
     */
    private $seeCallback;

    /**
     * Get the name of the element. This is a lowercase name of the called class.
     *
     * @return string
     */
    public function name(){
        return strtolower(class_basename(get_called_class()));
    }

    /**
     * Get the label of the element. This is a formatted name of the called class.
     *
     * @return string
     */
    public function label(){
        return Str::title(Str::snake(class_basename(get_called_class()), ' '));
    }

    /**
     * Set the permission of the element
     *
     * @param $callback Closure|boolean
     * @return $this
     */
    public function canSee($callback)
    {
        $this->seeCallback = $callback;
        return $this;
    }

    /**
     * Authorization to see the Element
     *
     * @return bool
     */
    public function authorizedToSee()
    {
        if (is_bool($this->seeCallback))
            return $this->seeCallback;

        if (is_callable($this->seeCallback))
            return call_user_func($this->seeCallback, $this);

        return true;
    }

}
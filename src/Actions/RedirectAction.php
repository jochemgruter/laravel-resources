<?php


namespace Gruter\ResourceViewer\Actions;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RedirectAction extends Action
{

    public $multiSelection = false;
    public $displayOnRow = true;
    public $displayOnRowWithLabel = true;

    public $callback;
    public $label;

    public $confirmation = false;

    public function  __construct($label, callable $callback)
    {
        $this->label = $label;
        $this->callback = $callback;
    }

    public function label()
    {
        return $this->label;
    }

    public function handle(Request $request, Collection $models)
    {
        $url = call_user_func($this->callback, $models->first());

        return redirect()->to($url);
    }

    public function fields()
    {

    }
}
<?php


namespace Gruter\ResourceViewer\Actions;


use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SimpleAction extends Action
{
    /**
     * @var string
     */
    private $label;
    /**
     * @var callable
     */
    private $callback;
    /**
     * @var array
     */
    private $fields;

    public function __construct(string $label, callable $callback)
    {
        $this->label = $label;
        $this->callback = $callback;
    }

    public function withFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    public function label()
    {
        return $this->label;
    }

    public function handle(Request $request, Collection $models)
    {
        return call_user_func($this->callback, $models);
    }

    protected function fields()
    {
        return $this->fields;
    }
}
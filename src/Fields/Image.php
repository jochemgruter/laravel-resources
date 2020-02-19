<?php


namespace Gruter\ResourceViewer\Fields;


class Image extends Field
{

    public $storagePath = null;

    public function __construct($attribute, $label = null)
    {
        $this->storagePath = storage_path();

        parent::__construct($attribute, $label);
    }

    public function storagePath($storagePath)
    {
        $this->storagePath = $storagePath;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function view($attribute, $value)
    {
        return view('ResourceViewer::fields.image', [
            'field' => $this,
            'attribute' => $attribute,
            'value' => $value
        ]);
    }
}
<?php


namespace Gruter\ResourceViewer\Tests\Fixtures;


use Gruter\ResourceViewer\Fields\ID;
use Gruter\ResourceViewer\Fields\Text;
use Gruter\ResourceViewer\Resource;

class Categories extends Resource
{

    public static $model = Category::class;

    public static $title = 'name';

    protected $canCreate = true;

    public static function uri()
    {
        return 'categories';
    }


    public function fields()
    {
        return [

            ID::make()
                ->hideOnForms(),

            Text::make('name'),

        ];
    }

    public function filters()
    {
        // TODO: Implement filters() method.
    }

    public function actions()
    {
        // TODO: Implement actions() method.
    }


}
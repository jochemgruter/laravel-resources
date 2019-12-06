<?php


namespace Gruter\ResourceViewer\Tests\Fixtures;



use Gruter\ResourceViewer\Fields\BelongsToMany;
use Gruter\ResourceViewer\Fields\ID;
use Gruter\ResourceViewer\Fields\Number;
use Gruter\ResourceViewer\Fields\Text;
use Gruter\ResourceViewer\PivotResource;
use Gruter\ResourceViewer\Resource;
use Illuminate\Database\Eloquent\Model;

class TestUsers extends Resource
{

    public static $model = TestUser::class;

    public static $title = 'name';

    protected $canCreate = true;
    protected $canEdit = true;
    protected $canSee = true;
    protected $canView = true;

    public static function uri()
    {
        return 'users';
    }


    public function fields()
    {
        return [
            ID::make()
                ->onlyOnIndex(),

            Text::make('name')
                ->rules('required'),

            Text::make('email')
                ->creationRules('email'),

            BelongsToMany::make(Categories::class, 'Categories', 'category_test_user')
                ->withFields([
                    Number::make('counter'),
                ])
                ->tapResource(function (PivotResource $resource){
                    $resource->canCreate()
                        ->canView()
                        ->canEdit();
                })
                ->uri('custom-pivot-uri')
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
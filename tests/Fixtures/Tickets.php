<?php


namespace Gruter\ResourceViewer\Tests\Fixtures;


use Gruter\ResourceViewer\Fields\BelongsTo;
use Gruter\ResourceViewer\Fields\ID;
use Gruter\ResourceViewer\Resource;

class Tickets extends Resource
{


    public static $model = Ticket::class;

    public static $title = 'subject';

    protected $with = ['user', 'category'];

    public function __construct()
    {
        $this->canSee = true;
        $this->canEdit = true;
    }


    protected function fields()
    {
        return [
            ID::make(),

            BelongsTo::make('user_id', 'User', TestUsers::class),

            BelongsTo::make('category_id', 'Category', Categories::class),

        ];
    }

    protected function filters()
    {
        // TODO: Implement filters() method.
    }

    protected function actions()
    {
        // TODO: Implement actions() method.
    }
}
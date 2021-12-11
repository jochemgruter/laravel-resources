# Resource viewer for laravel

~~Beautiful~~ designed CRUD package. Create your resources and assign Fields, Filters, Relations and Actions. This 
package is fully integratable in your own application. Some of the code architecture is inspired by Laravel Nova.   

## Example code 

Below a very small example how the Resource class is build.

```php
class Users extends Resource
{

    public static $model = User::class;

    public static $title = 'name';

    public function fields()
    {
        return [
            ID::make()
                ->hideOnCreate()
                ->hideOnUpdate(),

            Text::make('name')
                ->rules('required'),

            Text::make('email')
                ->displayUsing(function($value, $model){
                    return $value ?? '-';
                })
                ->rules('required', 'email'),
        ];
    }

    public function filters()
    {
        //
    }

    public function actions()
    {
        //
    }
}
```

## In development

~~Project is currently in development.~~

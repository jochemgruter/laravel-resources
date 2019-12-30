<?php

namespace Gruter\ResourceViewer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 02-02-19
 * Time: 12:21
 */

class ResourceServiceProvider extends ServiceProvider
{

    private $namespace = 'Gruter\ResourceViewer\Http\Controllers';


    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ResourceViewer');

        Route::namespace($this->namespace)
            ->middleware(config('resources.middleware'))
            ->group(__DIR__.'/../routes/web.php');


        View::composer('ResourceViewer::pages.*', 'Gruter\ResourceViewer\Http\ViewComposers\ResourceComposer');

        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/resource-viewer/'),
        ], 'public');

        $this->publishes([
            __DIR__.'/../config/resources.php' => config_path('resources.php'),
        ]);


        $this->registerGates();
    }

    public function register()
    {

        $this->app->bind('resource', ResourceManager::class);

        $this->mergeConfigFrom(
            __DIR__.'/../config/resources.php', 'resources'
        );

    }

    private function registerGates(){
        Gate::define('resource.index', function($user, Resource $resource){
            return $resource->authorizedToSee();
        });

        Gate::define('resource.create', function($user, Resource $resource){
            return $resource->authorizedToCreate();
        });

        Gate::define('resource.view', function($user, Resource $resource, Model $model){
            return $resource->authorizedToView($model);
        });

        Gate::define('resource.edit', function($user, Resource $resource, Model $model){
            return $resource->authorizedToEdit($model);
        });

        Gate::define('resource.assign', function($user, PivotResource $resource){
            return $resource->authorizedToAssign();
        });
    }
}
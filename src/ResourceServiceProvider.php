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
            ->middleware(config('resource-viewer.middleware'))
            ->group(__DIR__.'/../routes/web.php');


        View::composer('ResourceViewer::pages.*', 'Gruter\ResourceViewer\Http\ViewComposers\ResourceComposer');

        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/resource-viewer/'),
        ], 'public');

        $this->publishes([
            __DIR__.'/../config/resource-viewer.php' => config_path('resource-viewer.php'),
        ]);


        $this->registerGates();

        $this->extendCollection();
    }

    public function register()
    {

        $this->app->bind('resource', ResourceManager::class);

        $this->mergeConfigFrom(
            __DIR__.'/../config/resource-viewer.php', 'resource-viewer'
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

    private function extendCollection(){
        Collection::macro('sumUpToString', function($title, $label, $labelSingular = null, $copulative = 'and', $other = 'other'){
            if ($labelSingular == null)
                $labelSingular = Str::plural($label);

            switch($this->count()){
                case 0:
                    return '0 '.$label;

                case 1:
                    return $this->get(0)->$title .' '. $labelSingular;

                case 2:
                    return $this->get(0)->$title .' '.$copulative.' '. $this->get(1)->$title.' '. $label;

                case 3:
                    return $this->get(0)->$title.', '. $this->get(1)->$title.' '.$copulative.' '.$this->get(2)->$title.' '.$label;

                default:
                    return $this->get(0)->$title.', '.$this->get(1)->$title.' '.$copulative.' '.($this->count() - 2).' '.$other.' '.$label;
            }
        });
    }
}
<?php

namespace Gruter\ResourceViewer;

use Illuminate\Support\Collection;
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

class ResourseServiceProvider extends ServiceProvider
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

        /*Route::matched(function($event) {
            if($event->route->getName() == 'resource.index' || $event->route->getName() == 'resource.show')
                Session::put('resource.return_url', $event->request->getRequestUri());
        });*/

        $this->extendCollection();
    }

    public function register()
    {

        $this->app->bind('resources', function(){
            return new ResourceManager();
        });

        $this->mergeConfigFrom(
            __DIR__.'/../config/resource-viewer.php', 'resource-viewer'
        );

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
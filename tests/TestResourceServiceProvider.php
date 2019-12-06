<?php


namespace Gruter\ResourceViewer\Tests;

use Gruter\ResourceViewer\Facades\Resource;
use Gruter\ResourceViewer\ResourceServiceProvider;
use Gruter\ResourceViewer\Tests\Fixtures\Categories;
use Gruter\ResourceViewer\Tests\Fixtures\Category;
use Gruter\ResourceViewer\Tests\Fixtures\TestUsers;
use Gruter\ResourceViewer\Tests\Fixtures\Tickets;

class TestResourceServiceProvider extends ResourceServiceProvider
{
    public function boot()
    {
        parent::boot();

        $this->loadMigrationsFrom(__DIR__.'/Migrations/');

        \Event::listen('resource.booted', function($resource){
            if ($resource instanceof Categories){
                $resource->canSee(true);
                $resource->canCreate(function(){
                    return false;
                });
                $resource->canView(function($category){
                   return $category instanceof Category && $category->name == 'permission-test';
                });
            }
        });

    }

    public function register()
    {
        parent::register();

        Resource::register([
            TestUsers::class,
            Categories::class,
            Tickets::class
        ]);
    }


}
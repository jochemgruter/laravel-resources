<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 02-02-19
 * Time: 12:23
 */


$uris = implode('|', \Gruter\ResourceViewer\Facades\Resource::allUris());

Route::get(config('resource-viewer.route').'/{resource}', 'ResourceController@index')
    ->where('resource', $uris)
    ->name('resource.index');

Route::get(config('resource-viewer.route').'/{resource}/create', 'ResourceController@create')
    ->where('resource', $uris)
    ->name('resource.create');

Route::get(config('resource-viewer.route').'/{resource}/{id}', 'ResourceController@show')
    ->where('resource', $uris)
    ->name('resource.show'); //test

Route::get(config('resource-viewer.route').'/{resource}/{id}/edit', 'ResourceController@edit')
    ->where('resource', $uris)
    ->name('resource.edit');

Route::put(config('resource-viewer.route').'/{resource}/{id}', 'ResourceController@update')
    ->where('resource', $uris)
    ->name('resource.update');

Route::post(config('resource-viewer.route').'/{resource}', 'ResourceController@store')
    ->where('resource', $uris)
    ->name('resource.store');

Route::get(config('resource-viewer.route').'/{resource}/lookup', 'RelationController@lookup')
    ->where('resource', $uris)
    ->name('resource.lookup');

Route::get('/action/{resource}/{action}', 'ActionController@form')->name('resources.action_form');
Route::post('/action/{resource}/{action}', 'ActionController@form');
Route::post('/action-handler/{resource}/{action}', 'ActionController@handle')->name('resources.action_handler');

Route::get('/resources/style.css', function(){
    \Debugbar::disable();
    include(__DIR__.'/../public/style.css');
    return response('')->header('Content-Type', 'text/css');
});

Route::get('/resources/resources.js', function(){
    \Debugbar::disable();
    include(__DIR__.'/../public/resources.js');
    return response('')->header('Content-Type', 'text/javascript');
});
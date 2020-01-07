<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 02-02-19
 * Time: 12:23
 */


$uris = \Gruter\ResourceViewer\Facades\Resource::allUris();

if (count($uris['index']) > 0)
    Route::get(config('resources.route') . '/{resource}', 'ResourceController@index')
        ->where('resource', implode('|', $uris['index']))
        ->name('resource.index');

if (count($uris['create']) > 0)
    Route::get(config('resources.route') . '/{resource}/create', 'ResourceController@create')
        ->where('resource', implode('|', $uris['create']))
        ->name('resource.create');

if (count($uris['show']) > 0)
    Route::get(config('resources.route') . '/{resource}/{id}', 'ResourceController@show')
        ->where('resource', implode('|', $uris['show']))
        ->name('resource.show');

if (count($uris['edit']) > 0)
    Route::get(config('resources.route') . '/{resource}/{id}/edit', 'ResourceController@edit')
        ->where('resource', implode('|', $uris['edit']))
        ->name('resource.edit');

if (count($uris['update']) > 0)
    Route::put(config('resources.route') . '/{resource}/{id}', 'ResourceController@update')
        ->where('resource', implode('|', $uris['update']))
        ->name('resource.update');

if (count($uris['store']) > 0)
    Route::post(config('resources.route') . '/{resource}', 'ResourceController@store')
        ->where('resource', implode('|', $uris['store']))
        ->name('resource.store');

if (count($uris['lookup']) > 0)
    Route::get(config('resources.route') . '/{resource}/lookup', 'RelationController@lookup')
        ->where('resource', implode('|', $uris['lookup']))
        ->name('resource.lookup');

Route::get(config('resources.route') . '/{resource}/{id}/pivot/{pivot}/{pivotId}', 'ResourceController@show')
    ->name('resource.pivot.show');

Route::get(config('resources.route') . '/{resource}/{id}/pivot/{pivot}/{pivotId}/edit', 'ResourceController@edit')
    ->name('resource.pivot.edit');

Route::put(config('resources.route') . '/{resource}/{id}/pivot/{pivot}/{pivotId}', 'ResourceController@update')
    ->name('resource.pivot.update');

Route::post(config('resources.route') . '/{resource}/{id}/pivot/{pivot}', 'ResourceController@assign')
    ->name('resource.pivot.assign');


Route::post('/action/{resource}', 'ActionController@handle')->name('resources.action');

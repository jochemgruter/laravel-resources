<?php

namespace Gruter\ResourceViewer\Http\Controllers;

use Gruter\ResourceViewer\Contracts\Resource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 02-02-19
 * Time: 13:01
 */

class ResourceController extends Controller
{

    public function index(Request $request, $resource){

        $resource = Resource::findOrFailFromUri($resource);
        return $resource->index($request);
    }

    public function create(Request $request, $resource){
        $resource = Resource::findOrFailFromUri($resource);

        if (!$resource->canCreate())
            abort(403);

        return $resource->create($request);
    }

    public function show(Request $request, $resource, $id){
        $resource = Resource::findOrFailFromUri($resource);

        $model = ($resource::$model)::findOrFail($id);

        if (!$resource->canView($model))
            abort(403);

        return $resource->show($request, $model);
    }

    public function edit(Request $request, $resource, $id){
        $resource = Resource::findOrFailFromUri($resource);

        Session::put('resource.return_url', URL::previous());

        //dd(Session::get('resource.return_url'));

        $model = ($resource::$model)::findOrFail($id);

        if (!$resource->canEdit($model))
            abort(403);

        return $resource->edit($request, $model);
    }

    public function update(Request $request, $resource, $id){
        $resource = Resource::findOrFailFromUri($resource);

        $model = ($resource::$model)::findOrFail($id);

        if (!$resource->canEdit($model))
            abort(403);

        $validator = $resource->updateFormBuilder($model)->getValidator($request);

        foreach($resource->getFields('showOnUpdate') as $field){
            if ($request->has($field->attribute())) {
                $value = $request->get($field->attribute());

                $model->{$field->attribute()} = $value;
            }
        }

        return $resource->update($request, $model, $validator);
    }

    public function store(Request $request, $resource){
        $resource = Resource::findOrFailFromUri($resource);

        $validator = $resource->createFormBuilder()->getValidator($request);

        $model = $resource->newModel();

        foreach($resource->getFields('showOnCreate') as $field){
            $value = $request->get($field->attribute());

            if ($value != null)
                $model->{$field->attribute()} = $value;
        }

        return $resource->store($request, $model, $validator);
    }
}
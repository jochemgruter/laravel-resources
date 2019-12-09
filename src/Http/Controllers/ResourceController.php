<?php

namespace Gruter\ResourceViewer\Http\Controllers;

use Gruter\ResourceViewer\Facades\Resource;
use Gruter\ResourceViewer\Fields\BelongsToMany;
use Gruter\ResourceViewer\Fields\Field;
use Gruter\ResourceViewer\PivotResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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

    use AuthorizesRequests;

    public function index(Request $request, $resource)
    {
        $resource = Resource::findOrFailFromUri($resource);

        $this->authorize('resource.index', [$resource]);

        return $resource->index($request);
    }

    public function create(Request $request, $resource){
        $resource = Resource::findOrFailFromUri($resource);

        $this->authorize('resource.create', [$resource]);

        return $resource->create($request);
    }

    public function show(Request $request, $resource, $id, $pivot = null, $pivotId = null)
    {
        $resource = Resource::findOrFailFromUri($resource);
        $model = $resource->findOrFailModel($id);

        $this->authorize('resource.view', [$resource, $model]);

        if ($pivot != null && $pivotId != null){
            $resource = $this->getPivotResource($resource, $model, $pivot);
            $model = $resource->findOrFailModel($pivotId);

            $this->authorize('resource.view', [$resource, $model]);
        }

        return $resource->show($request, $model);
    }

    public function edit(Request $request, $resource, $id, $pivot = null, $pivotId = null)
    {
        $resource = Resource::findOrFailFromUri($resource);
        $model = $resource->findOrFailModel($id);

        Session::put('resource.return_url', URL::previous());

        $this->authorize('resource.edit', [$resource, $model]);

        if ($pivot != null && $pivotId != null){
            $resource = $this->getPivotResource($resource, $model, $pivot);
            $model = $resource->findOrFailModel($pivotId);

            $this->authorize('resource.edit', [$resource, $model]);
        }

        return $resource->edit($request, $model);
    }

    public function update(Request $request, $resource, $id, $pivot = null, $pivotId = null)
    {
        $resource = Resource::findOrFailFromUri($resource);

        $model = $resource->findOrFailModel($id);

        $this->authorize('resource.edit', [$resource, $model]);

        if ($pivot != null && $pivotId != null){
            $resource = $this->getPivotResource($resource, $model, $pivot);
            $model = $resource->findOrFailModel($pivotId);

            $this->authorize('resource.edit', [$resource, $model]);
        }

        $validator = $resource->makeUpdateForm($model)->getValidator($request);

        foreach($resource->getFields('showOnUpdate') as $field){
            if ($request->has($field->attribute())) {
                $value = $request->get($field->attribute());

                $model->{$field->attribute()} = $value;
            }
        }

        return $resource->update($request, $model, $validator);
    }

    public function store(Request $request, $resource)
    {
        $resource = Resource::findOrFailFromUri($resource);

        $this->authorize('resource.create', [$resource]);

        $validator = $resource->makeCreationForm()->getValidator($request);

        $model = $resource->newModel();

        foreach($resource->getFields('showOnCreate') as $field){
            $value = $request->get($field->attribute());

            if ($value != null)
                $model->{$field->attribute()} = $value;
        }

        return $resource->store($request, $model, $validator);
    }

    public function assign(Request $request, $resource, $id, $pivot)
    {
        $parentResource = Resource::findOrFailFromUri($resource);
        $parentModel = $parentResource->findOrFailModel($id);

        $resource = $this->getPivotResource($parentResource, $parentModel, $pivot);

        $this->authorize('resource.assign', $resource);

        $resource->makeAssignForm()->validate($request);

        $data = $resource->getFields('showOnCreate')->mapWithKeys(function (Field $field) use ($request){
            if ($value = $request->get($field->attribute))
                return [$field->attribute => $value];

            return [];
        })->forget($resource->relatedPivotKey)->toArray();

        $resource->newQuery()->attach($request->get($resource->relatedPivotKey), $data);

        return redirect()->back()->with(['success' => 'Successfully added']);
    }


    /**
     * @param $resource
     * @param $model
     * @param $pivotUri
     * @return PivotResource|null
     */
    private function getPivotResource(\Gruter\ResourceViewer\Resource $resource, $model, $pivotUri){
        $field = $resource->getFields()->first(function(Field $field) use ($pivotUri){
            return $field instanceof BelongsToMany && $field->uri == $pivotUri;
        });
        if ($field != null)
            return $field->makePivotResource($model);

        return null;
    }
}
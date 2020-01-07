<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 07-02-19
 * Time: 16:28
 */

namespace Gruter\ResourceViewer\Http\Controllers;


use Gruter\ResourceViewer\Facades\Resource;
use Illuminate\Http\Request;

class ActionController
{

    public function handle(Request $request, $resource){

        $request->validate([
            '__action' => 'required|numeric',
            '__ids' => 'required|array',
        ]);

        $resource = Resource::findOrFailFromUri($resource);
        $action = $resource->getAction($request->get('__action'));

        if ($action == null)
            abort(404);

        if(!$action->authorizedToSee())
            abort(403);

        if ($action->hasForm()){
            $form = $action->makeForm();
            $validator = $form->getValidator($request);
            if ($validator->fails()){

                return redirect()->back()
                    ->withInput()
                    ->withErrors($validator->errors())
                    ->getSession()->flash('actionFailed', $action->index());
            }
        }

        $model = $resource::$model;

        if ($request->has('__allMatching')){
            $models = $resource->getQuery()->get();
        }else {
            $models = $model::whereIn($resource->newModel()->getKeyName(), $request->get('__ids', []))->get();
        }

        if ($models->count() == 0)
            return back()->withInput()->withErrors(['No items selected to run action']);

        list($models, $modelsDenied) = $models->partition(function($model) use ($action){
           return $action->authorizedToRun($model);
        });

        if ($models->count() > 0)
            $result = $action->handle($request, $models);

        if (is_null($result ?? null))
            $result = true;

        if (is_bool($result)){
            if ($result){
                $errors = $modelsDenied->count() > 0 ? ['Could not run '.$action->label().' over '.$modelsDenied->count().' items'] : [];

                return redirect()->back()
                    ->with(['success' => $action->label().' successfully run over '.$models->count().' items'])
                    ->withErrors($errors);
            }else{
                return redirect()->back()->withErrors(['Something went wrong']);
            }
        }

        return $result;
    }

}
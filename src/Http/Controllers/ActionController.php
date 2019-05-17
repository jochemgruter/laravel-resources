<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 07-02-19
 * Time: 16:28
 */

namespace Gruter\ResourceViewer\Http\Controllers;


use Gruter\ResourceViewer\Actions\Action;
use Gruter\ResourceViewer\Contracts\Resource;
use Gruter\ResourceViewer\Fields\Boolean;
use Gruter\ResourceViewer\FormBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Session;

class ActionController
{

    public function handle(Request $request, $resource, $action){

        $resource = Resource::findOrFail($resource);

        $action = $resource->getAction($action);

        if ($action == null)
            abort(404);

        $model = $resource::$model;

        if ($request->has('__all') && $request->get('__all') == 1){
            $models = $resource->getQuery()->get();
        }else {
            $models = $model::whereIn($resource->newModel()->getKeyName(), $request->get('__ids'))->get();
        }

        $modelsDenied = collect();

        if (!$action->authorizedToRun($models))
            return $this->makeResponse($action, 'MESSAGE', ['message' => 'Kan de actie niet uitvoeren op de models.']);

        $force = $request->has('force') && $request->get('force') == 1;

        if ($modelsDenied->count() > 0 && !$force  || $models->count() == 0) {
            $message = 'Geen toegang om deze actie uit te voeren voor ' .
                $modelsDenied->sumUpToString($resource::$title, strtolower($resource::label()), strtolower($resource::labelSingular())).'.';

            if ($models->count() > 0){
                $message .= 'Wil je deze actie over '.$models->count().' '.$resource::label().' uitvoeren?';

                return $this->makeResponse($action, 'QUESTION', ['message' => $message]);
            }

            return $this->makeResponse($action, 'MESSAGE', ['message' => $message]);
        }

        $fields = $action->fields($models);

        if ($fields != null && count($fields) > 0)
            return $this->makeResponse($action, 'FORM', ['resource' => $resource->uri()]);


        if (($fields == null || count($fields) == 0) && $action->confirmation && !$force){
            $message = 'Weet je zeker dat je deze actie wilt uitvoeren voor '.
                $models->sumUpToString($resource::$title, strtolower($resource::label()), strtolower($resource::labelSingular()));
            return $this->makeResponse($action, 'QUESTION', ['message' => $message]);
        }

        if(!$action->async && $request->has('__async'))
            return $this->makeResponse($action, 'RUN_NO_ASYNC');

        $result = $action->handle($request, $models);

        if (!$action->async)
            return $result;

        if (is_null($result))
            $result = true;

        if (is_bool($result)){
            if ($result){
                $result = 'De actie is succesvol uitgevoerd';
            }else{
                $result = 'Er is iets foutgegaan tijdens het uitvoeren van de actie.';
            }
        }

        return $this->makeResponse($action, 'MESSAGE', ['message' => $result]);
    }

    public function form(Request $request, $resource, $action){
        $resource = Resource::findOrFailFromUri($resource);
        $action = $resource->getAction($action);
        $model = $resource::$model;

        if ($action == null)
            abort(404);

        $models = null;
        if ($request->has('ids')){
            $models = $model::whereIn($resource->newModel()->getKeyName(), explode(',',$request->get('ids')))->get();
        }else{
            $models = $resource->getQuery()->get();
        }

        if (!$action->authorizedToRun($models))
            return $this->makeResponse($action, 'MESSAGE', ['message' => 'Kan de actie niet uitvoeren op de models.']);

        $fields = $action->fields($models);
        $form = new FormBuilder($fields, $request->fullUrl());
        $form->defaultEmpty();
        $form->actionButton = 'Run';


        if ($request->isMethod('post')){
            $form->getValidator($request)->validate();

            $result = $action->handle($request, $models);

            if (is_null($result))
                $result = true;

            if (is_bool($result)){
                $returnUrl = Session::get('resource.return_url', null);
                if ($returnUrl == null)
                    $returnUrl = $models->count() == 1 ? $resource->route('show', $models[0]->getKey()) : $resource->route('index');

                if ($result){
                    return redirect()->to($returnUrl)->with(['success' => 'De actie is succesvol uitgevoerd']);
                }else{
                    return redirect()->to($returnUrl)->withErrors(['Er is iets foutgegaan tijdens het uitvoeren van de actie.']);
                }
            }
            return $result;
        }else{
            Session::put('resource.return_url', URL::previous());
        }

        return view('ResourceViewer::pages.action', ['action' => $action, 'form' => $form, 'models' => $models, 'resource' => $resource]);
    }

    private function makeResponse(Action $action, $response, array $data = []){
        $response = array_merge(['response' => $response, 'action' => $action->label()], $data);
        return $response;
    }

}
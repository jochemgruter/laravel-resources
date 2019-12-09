<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 05-03-19
 * Time: 14:38
 */

namespace Gruter\ResourceViewer\Http\Controllers;


use Gruter\ResourceViewer\Facades\Resource;
use Illuminate\Http\Request;

class RelationController
{

    public function directLookup(Request $request, $resource, $id){

    }

    public function lookup(Request $request, $resource){

        $resource = Resource::findOrFailFromUri($resource);

        return $resource->hideActions()->rows(10)->renderIndexTable();
    }


}
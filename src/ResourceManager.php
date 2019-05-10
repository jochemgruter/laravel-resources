<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 02-02-19
 * Time: 12:51
 */

namespace Gruter\ResourceViewer;

class ResourceManager
{

    /**
     * @var array Resource
     */
    private $resources;

    public function register($resource){
        $this->resources[$resource::name()] = $resource;
    }

    public function get($name){
        if (isset($this->resources[$name])){
            return $this->resources[$name];
        }
        return null;
    }

    public function fromUri($uri){
        foreach($this->resources as $resource){
            if ($resource::uri() == $uri){
                return $resource;
            }
        }
        return null;
    }

    public function all(){
        return array_values($this->resources);
    }

    public function allUris(){
        $uris = [];
        foreach($this->resources as $resource){
            $uris[] = $resource::uri();
        }
        return $uris;
    }

    public function findOrFail($resource){
        $resource = $this->get($resource);

        if ($resource != null){
            $resource = new $resource;
            return $resource;
        }
        abort(404);
    }

    public function findOrFailFromUri($resource){
        $resource = $this->fromUri($resource);

        if ($resource != null){
            $resource = new $resource;
            return $resource;
        }
        abort(404);
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: jochemgruter
 * Date: 02-02-19
 * Time: 12:51
 */

namespace Gruter\ResourceViewer;

use Gruter\ResourceViewer\Events\ResourceBooted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResourceManager
{

    /**
     * All registered resources
     *
     * @var array
     */
    private $resources = [];

    public function register($resources){
        if (!is_array($resources)) {
            $resources = [$resources];
        }
        $this->resources = array_merge($this->resources, $resources);
    }

    /**
     * Returns the classpath of a Resource
     *
     * @param  string $name class basename or classpath
     * @return string
     */
    public function get($name)
    {
        if (in_array($name, $this->resources))
            return $name;

        foreach($this->resources as $resource){
            if (class_basename($resource) == $name)
                return $resource;
        }
    }

    /**
     * Returns the classpath of a Resource matched by the given uri
     *
     * @param  string  $uri
     * @return string
     */
    public function fromUri($uri)
    {
        foreach($this->resources as $resource){
            if ($resource::uri() == $uri){
                return $resource;
            }
        }
    }

    /**
     * Get all registered resources
     *
     * @return array
     */
    public function all(){
        return array_values($this->resources);
    }

    /**
     * Get all available routes of the registered resources.
     *
     * @return array
     */
    public function allUris(){
        $uris = ['index' => [], 'create' => [], 'show' => [], 'edit' => [], 'update' => [], 'store' => [], 'lookup' => []];

        foreach($this->resources as $resource){
            foreach ($uris as $key => $value){
                if(in_array($key, $resource::routes())){
                    $uris[$key][] = $resource::uri();
                }
            }
        }

        return $uris;
    }

    /**
     * Get and initiates a Resource
     *
     * @param  string $resource
     * @return Resource|null
     */
    public function find($resource){
        $resource = $this->get($resource);

        if ($resource != null){
            $resource = new $resource;
            return $resource;
        }
    }

    /**
     * Get and initiates a Resource
     *
     * @param  string $resource
     * @return Resource
     *
     * @throws NotFoundHttpException
     */
    public function findOrFail($resource){
        $resource = $this->find($resource);
        if ($resource != null)
            return $resource;

        throw new NotFoundHttpException();
    }

    /**
     * Get and initiates a Resource for the given uri
     *
     * @param  string $uri
     * @return Resource
     */
    public function findOrFailFromUri($uri){
        $resource = $this->fromUri($uri);

        if ($resource != null){
            $resource = new $resource;
            return $resource;
        }

        throw new NotFoundHttpException();
    }

}
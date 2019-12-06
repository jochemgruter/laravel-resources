<?php


namespace Gruter\ResourceViewer\Tests\Feature;


use Gruter\ResourceViewer\Facades\Resource;
use Gruter\ResourceViewer\Tests\Fixtures\Category;
use Gruter\ResourceViewer\Tests\ResourceTest;

class ResourceAuthorisationTest extends ResourceTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }


    public function test_resource_authorisation()
    {
        //$this->withoutExceptionHandling();

        // permissions of categories resource are set in service provider with event listener

        $resource = Resource::find('Categories');

        $response = $this->get(route('resource.index', ['resource' => $resource::uri()]));

        $response->assertOk();

        $response = $this->get(route('resource.create', ['resource' => $resource::uri()]));
        $response->assertForbidden();

        $category = factory(Category::class)->create(['name' => 'permission-test']);

        $route = route('resource.show', ['resource' => $resource::uri(), 'id' => $category->id]);
        $response = $this->get($route);
        $response->assertOk();
    }

}
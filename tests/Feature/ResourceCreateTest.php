<?php


namespace Gruter\ResourceViewer\Tests\Feature;


use Gruter\ResourceViewer\Tests\Fixtures\Category;
use Gruter\ResourceViewer\Tests\Fixtures\TestUser;
use Gruter\ResourceViewer\Tests\Fixtures\Ticket;
use Gruter\ResourceViewer\Tests\Fixtures\Tickets;
use Gruter\ResourceViewer\Tests\ResourceTest;

class ResourceCreateTest extends ResourceTest
{


    public function test_create_new_model(){


        $this->withoutExceptionHandling();

        $response = $this->get('/testing/users/create');
        $response->assertOk();

        $response = $this->post('/testing/users',
            [
                'name' => 'Test TestUser',
                'email' => 'test@domain.com',
            ]);

        $response->assertRedirect('/testing/users');
        $this->assertDatabaseHas('testing_users', [
            'email' => 'test@domain.com'
        ]);
    }


    public function test_resource_index(){
        $ticket = factory(Ticket::class)->create();

        $response = $this->get('/testing/tickets');
        $response->assertOk();

    }
}
<?php


namespace Gruter\ResourceViewer\Tests\Feature;


use Gruter\ResourceViewer\Facades\Resource;
use Gruter\ResourceViewer\Tests\Fixtures\Ticket;
use Gruter\ResourceViewer\Tests\ResourceTest;

class BelongsToTests extends ResourceTest
{

    public function test_belongs_to_view()
    {
        $ticket = factory(Ticket::class)->create();

        $resource = Resource::find('tickets');
        $field = $resource->getField('category_id');

        $value = $field->display($ticket);
        $url = $field->getRelatedLink($ticket);

        $this->assertTrue(true);
    }

}
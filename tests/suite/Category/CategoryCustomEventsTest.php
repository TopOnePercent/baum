<?php

use Illuminate\Support\Facades\Event;

class CategoryCustomEventsTest extends CategoryTestCase
{
    public function setUp()
    {
        parent::setUp();

        Event::fake();
    }

    public function testMovementEventsFire()
    {
        $node = $this->categories('Child 1');
        $node->moveToRightOf($this->categories('Child 3'));

        Event::assertDispatched('eloquent.moving: ' . Category::class, function ($event, $object) use ($node) {
            return $object->id == $node->id;
        });

        Event::assertDispatched('eloquent.moved: ' . Category::class, function ($event, $object) use ($node) {
            return $object->id == $node->id;
        });
    }

    public function testMovementHaltsWhenReturningFalseFromMoving()
    {
        $node = $this->categories('Child 2');

        // $events->shouldReceive('until')->once()->with('eloquent.moving: '.get_class($node), $node)->andReturn(false);

        // Force "moving" to return false
        Category::moving(function ($node) {
            return false;
        });

        $node->makeRoot();
        $node->reload();

        // Event::assertDispatched('eloquent.moving: ' . Category::class, function ($event, $object) use ($node) {
        //     return $object->id == $node->id;
        // });

        // $this->assertEquals(1, $node->getParentId());
        // $this->assertEquals(1, $node->getLevel());
        // $this->assertEquals(4, $node->getLeft());
        // $this->assertEquals(7, $node->getRight());
    }
}

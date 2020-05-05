<?php

namespace Baum\Tests\Suite\Category;

use Baum\Tests\Suite\Models\Category;
use Illuminate\Support\Facades\Event;

class CategoryCustomEventsTest extends CategoryAbstract
{
    public function testMovingEventFired()
    {
        Event::fake();

        $node = $this->categories('Child 2');

        $node->makeRoot();
        $node->reload();

        Event::assertDispatched('eloquent.moving: '.get_class($node), function ($event, $object) use ($node) {
            return $object->id == $node->id;
        });
    }

    public function testMovementEventsFire()
    {
        Event::fake();

        $node = $this->categories('Child 1');
        $node->moveToRightOf($this->categories('Child 3'));

        Event::assertDispatched('eloquent.moving: '.get_class($node), function ($event, $object) use ($node) {
            return $object->id == $node->id;
        });

        Event::assertDispatched('eloquent.moved: '.get_class($node), function ($event, $object) use ($node) {
            return $object->id == $node->id;
        });
    }

    public function testDoesNotMoveWhenReturningFalseFromMoving()
    {
        $node = $this->categories('Child 2');

        Category::moving(function ($node) {
            return false;
        });

        $node->makeRoot();
        $node->reload();

        $this->assertEquals(1, $node->getParentId());
        $this->assertEquals(1, $node->getLevel());
        $this->assertEquals(4, $node->getLeft());
        $this->assertEquals(7, $node->getRight());
    }

    public function testDoesMoveWhenReturningTrueFromMoving()
    {
        $node = $this->categories('Child 2');

        Category::moving(function ($node) {
            return true;
        });

        $node->makeRoot();
        $node->reload();

        $this->assertEquals(null, $node->getParentId());
        $this->assertEquals(0, $node->getLevel());
        $this->assertEquals(9, $node->getLeft());
        $this->assertEquals(12, $node->getRight());
    }
}

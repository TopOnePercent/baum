<?php

namespace Baum\Tests\Main\Standard;

use Baum\Tests\Main\Concerns\NodeModelExtensionsTest;
use Baum\Tests\Main\Models\Category;
use Baum\Tests\Main\Support\PopulateData;
use Baum\Tests\Main\UnitAbstract;
use Illuminate\Support\Facades\Event;

class CategoryCustomEventsTest extends UnitAbstract
{
    use NodeModelExtensionsTest;

    public function testMovingEventFired()
    {
        Event::fake();

        $node = Category::create(['name' => 'Child 2']);

        //$node = $this->categories('Child 2');

        $node->makeRoot();
        $node->reload();

        Event::assertDispatched('eloquent.moving: '.get_class($node), function ($event, $object) use ($node) {
            return $object->id == $node->id;
        });
    }

    public function testMovementEventsFire()
    {
        // Event::fake();

        $build = Category::buildTree(PopulateData::basicTree());
        $this->assertTrue(Category::isValidNestedSet());
        $alpha = Category::find(1);
        $bravo = Category::find(4);

        //$alpha = Category::create(['name' => 'alpha']);
        //$bravo = Category::create(['name' => 'bravo']);
        //$charlie = Category::create(['name' => 'charlie']);
        $alpha->moveToRightOf($bravo);

//         Event::assertDispatched('eloquent.moving: '.get_class($node), function ($event, $object) use ($node) {
//             return $object->id == $node->id;
//         });
//
//         Event::assertDispatched('eloquent.moved: '.get_class($node), function ($event, $object) use ($node) {
//             return $object->id == $node->id;
//         });
    }

    public function testDoesNotMoveWhenReturningFalseFromMoving()
    {
        $build = Category::buildTree(PopulateData::basicTree());
        $node = Category::find(2);

        Category::moving(function ($node) {
            return false;
        });

        $node->makeRoot();
        $node->reload();

        $this->assertEquals(1, $node->getParentId());
        $this->assertEquals(1, $node->getLevel());
        $this->assertEquals(2, $node->getLeft());
        $this->assertEquals(3, $node->getRight());
    }

    public function testDoesMoveWhenReturningTrueFromMoving()
    {
        $build = Category::buildTree(PopulateData::basicTree());
        $node = Category::find(2);

        Category::moving(function ($node) {
            return true;
        });

        $node->makeRoot();
        $node->reload();

        $this->assertEquals(null, $node->getParentId());
        $this->assertEquals(0, $node->getLevel());
        $this->assertEquals(11, $node->getLeft());
        $this->assertEquals(12, $node->getRight());
    }
}

<?php

use Baum\Tests\Main\UnitAbstract;
use Baum\Tests\Main\Models\Cluster;

// @codingStandardsIgnoreLine
class ClusterMovementTest extends UnitAbstract
{
    protected function setUp(): void
    {
        parent::setUp();

        $root_1 = Cluster::create(['name' => 'Root 1']);

        $child_1 = Cluster::create(['name' => 'Child 1']);
        $child_1->makeChildOf($root_1);

        $child_2 = Cluster::create(['name' => 'Child 2']);
        $child_2->makeChildOf($root_1);
        $child_2_1 = Cluster::create(['name' => 'Child 2.1']);
        $child_2_1->makeChildOf($child_2);

        $child_3 = Cluster::create(['name' => 'Child 3']);
        $child_3->makeChildOf($root_1);

        $root_2 = Cluster::create(['name' => 'Root 2']);
    }

    public function testMoveLeft()
    {
        Cluster::clusters('Child 2')->moveLeft();

        $this->assertNull(Cluster::clusters('Child 2')->getLeftSibling());

        $this->assertEquals(Cluster::clusters('Child 1'), Cluster::clusters('Child 2')->getRightSibling());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @expectedException Baum\Exceptions\MoveNotPossibleException
     */
    public function testMoveLeftRaisesAnExceptionWhenNotPossible()
    {
        $node = Cluster::clusters('Child 2');

        $node->moveLeft();

        try {
            $node->moveLeft();
        } catch (\Exception $error) {
            $this->assertInstanceOf(\Baum\Exceptions\MoveNotPossibleException::class, $error);
        }
    }

    public function testMoveLeftDoesNotChangeDepth()
    {
        Cluster::clusters('Child 2')->moveLeft();

        $this->assertEquals(1, Cluster::clusters('Child 2')->getDepth());
        $this->assertEquals(2, Cluster::clusters('Child 2.1')->getDepth());
    }

    public function testMoveLeftWithSubtree()
    {
        Cluster::clusters('Root 2')->moveLeft();

        $this->assertNull(Cluster::clusters('Root 2')->getLeftSibling());
        $this->assertEquals(Cluster::clusters('Root 1'), Cluster::clusters('Root 2')->getRightSibling());
        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals(0, Cluster::clusters('Root 1')->getDepth());
        $this->assertEquals(0, Cluster::clusters('Root 2')->getDepth());

        $this->assertEquals(1, Cluster::clusters('Child 1')->getDepth());
        $this->assertEquals(1, Cluster::clusters('Child 2')->getDepth());
        $this->assertEquals(1, Cluster::clusters('Child 3')->getDepth());

        $this->assertEquals(2, Cluster::clusters('Child 2.1')->getDepth());
    }

    public function testMoveToLeftOf()
    {
        Cluster::clusters('Child 3')->moveToLeftOf(Cluster::clusters('Child 1'));

        $this->assertNull(Cluster::clusters('Child 3')->getLeftSibling());

        $this->assertEquals(Cluster::clusters('Child 1'), Cluster::clusters('Child 3')->getRightSibling());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @expectedException Baum\Exceptions\MoveNotPossibleException
     */
    public function testMoveToLeftOfRaisesAnExceptionWhenNotPossible()
    {
        try {
            Cluster::clusters('Child 1')->moveToLeftOf(Cluster::clusters('Child 1')->getLeftSibling());
        } catch (\Exception $error) {
            $this->assertInstanceOf(\Baum\Exceptions\MoveNotPossibleException::class, $error);
        }
    }

    public function testMoveToLeftOfDoesNotChangeDepth()
    {
        Cluster::clusters('Child 2')->moveToLeftOf(Cluster::clusters('Child 1'));

        $this->assertEquals(1, Cluster::clusters('Child 2')->getDepth());
        $this->assertEquals(2, Cluster::clusters('Child 2.1')->getDepth());
    }

    public function testMoveToLeftOfWithSubtree()
    {
        Cluster::clusters('Root 2')->moveToLeftOf(Cluster::clusters('Root 1'));

        $this->assertNull(Cluster::clusters('Root 2')->getLeftSibling());
        $this->assertEquals(Cluster::clusters('Root 1'), Cluster::clusters('Root 2')->getRightSibling());
        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals(0, Cluster::clusters('Root 1')->getDepth());
        $this->assertEquals(0, Cluster::clusters('Root 2')->getDepth());

        $this->assertEquals(1, Cluster::clusters('Child 1')->getDepth());
        $this->assertEquals(1, Cluster::clusters('Child 2')->getDepth());
        $this->assertEquals(1, Cluster::clusters('Child 3')->getDepth());

        $this->assertEquals(2, Cluster::clusters('Child 2.1')->getDepth());
    }

    public function testMoveRight()
    {
        Cluster::clusters('Child 2')->moveRight();

        $this->assertNull(Cluster::clusters('Child 2')->getRightSibling());

        $this->assertEquals(Cluster::clusters('Child 3'), Cluster::clusters('Child 2')->getLeftSibling());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @expectedException Baum\Exceptions\MoveNotPossibleException
     */
    public function testMoveRightRaisesAnExceptionWhenNotPossible()
    {
        $node = Cluster::clusters('Child 2');

        $node->moveRight();

        try {
            $node->moveRight();
        } catch (\Exception $error) {
            $this->assertInstanceOf(\Baum\Exceptions\MoveNotPossibleException::class, $error);
        }
    }

    public function testMoveRightDoesNotChangeDepth()
    {
        Cluster::clusters('Child 2')->moveRight();

        $this->assertEquals(1, Cluster::clusters('Child 2')->getDepth());
        $this->assertEquals(2, Cluster::clusters('Child 2.1')->getDepth());
    }

    public function testMoveRightWithSubtree()
    {
        Cluster::clusters('Root 1')->moveRight();

        $this->assertNull(Cluster::clusters('Root 1')->getRightSibling());
        $this->assertEquals(Cluster::clusters('Root 2'), Cluster::clusters('Root 1')->getLeftSibling());
        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals(0, Cluster::clusters('Root 1')->getDepth());
        $this->assertEquals(0, Cluster::clusters('Root 2')->getDepth());

        $this->assertEquals(1, Cluster::clusters('Child 1')->getDepth());
        $this->assertEquals(1, Cluster::clusters('Child 2')->getDepth());
        $this->assertEquals(1, Cluster::clusters('Child 3')->getDepth());

        $this->assertEquals(2, Cluster::clusters('Child 2.1')->getDepth());
    }

    public function testMoveToRightOf()
    {
        Cluster::clusters('Child 1')->moveToRightOf(Cluster::clusters('Child 3'));

        $this->assertNull(Cluster::clusters('Child 1')->getRightSibling());

        $this->assertEquals(Cluster::clusters('Child 3'), Cluster::clusters('Child 1')->getLeftSibling());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @expectedException Baum\Exceptions\MoveNotPossibleException
     */
    public function testMoveToRightOfRaisesAnExceptionWhenNotPossible()
    {
        try {
            Cluster::clusters('Child 3')->moveToRightOf(Cluster::clusters('Child 3')->getRightSibling());
        } catch (\Exception $error) {
            $this->assertInstanceOf(\Baum\Exceptions\MoveNotPossibleException::class, $error);
        }
    }

    public function testMoveToRightOfDoesNotChangeDepth()
    {
        Cluster::clusters('Child 2')->moveToRightOf(Cluster::clusters('Child 3'));

        $this->assertEquals(1, Cluster::clusters('Child 2')->getDepth());
        $this->assertEquals(2, Cluster::clusters('Child 2.1')->getDepth());
    }

    public function testMoveToRightOfWithSubtree()
    {
        Cluster::clusters('Root 1')->moveToRightOf(Cluster::clusters('Root 2'));

        $this->assertNull(Cluster::clusters('Root 1')->getRightSibling());
        $this->assertEquals(Cluster::clusters('Root 2'), Cluster::clusters('Root 1')->getLeftSibling());
        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals(0, Cluster::clusters('Root 1')->getDepth());
        $this->assertEquals(0, Cluster::clusters('Root 2')->getDepth());

        $this->assertEquals(1, Cluster::clusters('Child 1')->getDepth());
        $this->assertEquals(1, Cluster::clusters('Child 2')->getDepth());
        $this->assertEquals(1, Cluster::clusters('Child 3')->getDepth());

        $this->assertEquals(2, Cluster::clusters('Child 2.1')->getDepth());
    }

    public function testMakeRoot()
    {
        Cluster::clusters('Child 2')->makeRoot();

        $newRoot = Cluster::clusters('Child 2');

        $this->assertNull($newRoot->parent()->first());
        $this->assertEquals(0, $newRoot->getLevel());
        $this->assertEquals(9, $newRoot->getLeft());
        $this->assertEquals(12, $newRoot->getRight());

        $this->assertEquals(1, Cluster::clusters('Child 2.1')->getLevel());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testNullifyParentColumnMakesItRoot()
    {
        $node = Cluster::clusters('Child 2');

        $node->parent_id = null;

        $node->save();

        $this->assertNull($node->parent()->first());
        $this->assertEquals(0, $node->getLevel());
        $this->assertEquals(9, $node->getLeft());
        $this->assertEquals(12, $node->getRight());

        $this->assertEquals(1, Cluster::clusters('Child 2.1')->getLevel());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testNullifyParentColumnOnNewNodes()
    {
        $node = new Cluster(['name' => 'Root 3']);

        $node->parent_id = null;

        $node->save();

        $node->reload();

        $this->assertNull($node->parent()->first());
        $this->assertEquals(0, $node->getLevel());
        $this->assertEquals(13, $node->getLeft());
        $this->assertEquals(14, $node->getRight());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testNewClusterWithNullParent()
    {
        $node = new Cluster(['name' => 'Root 3']);
        $this->assertTrue($node->isRoot());

        $node->save();
        $this->assertTrue($node->isRoot());

        $node->makeRoot();
        $this->assertTrue($node->isRoot());
    }

    public function testMakeChildOf()
    {
        Cluster::clusters('Child 1')->makeChildOf(Cluster::clusters('Child 3'));

        $this->assertEquals(Cluster::clusters('Child 3'), Cluster::clusters('Child 1')->parent()->first());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testMakeChildOfAppendsAtTheEnd()
    {
        $newChild = Cluster::create(['name' => 'Child 4']);

        $newChild->makeChildOf(Cluster::clusters('Root 1'));

        $lastChild = Cluster::clusters('Root 1')->children()->get()->last();
        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testMakeChildOfMovesWithSubtree()
    {
        Cluster::clusters('Child 2')->makeChildOf(Cluster::clusters('Child 1'));

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals(Cluster::clusters('Child 1')->getKey(), Cluster::clusters('Child 2')->getParentId());

        $this->assertEquals(3, Cluster::clusters('Child 2')->getLeft());
        $this->assertEquals(6, Cluster::clusters('Child 2')->getRight());

        $this->assertEquals(2, Cluster::clusters('Child 1')->getLeft());
        $this->assertEquals(7, Cluster::clusters('Child 1')->getRight());
    }

    public function testMakeChildOfSwappingRoots()
    {
        $newRoot = Cluster::create(['name' => 'Root 3']);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        Cluster::clusters('Root 2')->makeChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Cluster::clusters('Root 2')->getParentId());

        $this->assertEquals(12, Cluster::clusters('Root 2')->getLeft());
        $this->assertEquals(13, Cluster::clusters('Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    public function testMakeChildOfSwappingRootsWithSubtrees()
    {
        $newRoot = Cluster::create(['name' => 'Root 3']);

        Cluster::clusters('Root 1')->makeChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Cluster::clusters('Root 1')->getParentId());

        $this->assertEquals(4, Cluster::clusters('Root 1')->getLeft());
        $this->assertEquals(13, Cluster::clusters('Root 1')->getRight());

        $this->assertEquals(8, Cluster::clusters('Child 2.1')->getLeft());
        $this->assertEquals(9, Cluster::clusters('Child 2.1')->getRight());
    }

    public function testMakeFirstChildOf()
    {
        Cluster::clusters('Child 1')->makeFirstChildOf(Cluster::clusters('Child 3'));

        $this->assertEquals(Cluster::clusters('Child 3'), Cluster::clusters('Child 1')->parent()->first());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testMakeFirstChildOfAppendsAtTheBeginning()
    {
        $newChild = Cluster::create(['name' => 'Child 4']);

        $newChild->makeFirstChildOf(Cluster::clusters('Root 1'));

        $lastChild = Cluster::clusters('Root 1')->children()->get()->first();
        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testMakeFirstChildOfMovesWithSubtree()
    {
        Cluster::clusters('Child 2')->makeFirstChildOf(Cluster::clusters('Child 1'));

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals(Cluster::clusters('Child 1')->getKey(), Cluster::clusters('Child 2')->getParentId());

        $this->assertEquals(3, Cluster::clusters('Child 2')->getLeft());
        $this->assertEquals(6, Cluster::clusters('Child 2')->getRight());

        $this->assertEquals(2, Cluster::clusters('Child 1')->getLeft());
        $this->assertEquals(7, Cluster::clusters('Child 1')->getRight());
    }

    public function testMakeFirstChildOfSwappingRoots()
    {
        $newRoot = Cluster::create(['name' => 'Root 3']);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        Cluster::clusters('Root 2')->makeFirstChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Cluster::clusters('Root 2')->getParentId());

        $this->assertEquals(12, Cluster::clusters('Root 2')->getLeft());
        $this->assertEquals(13, Cluster::clusters('Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    public function testMakeFirstChildOfSwappingRootsWithSubtrees()
    {
        $newRoot = Cluster::create(['name' => 'Root 3']);

        Cluster::clusters('Root 1')->makeFirstChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Cluster::clusters('Root 1')->getParentId());

        $this->assertEquals(4, Cluster::clusters('Root 1')->getLeft());
        $this->assertEquals(13, Cluster::clusters('Root 1')->getRight());

        $this->assertEquals(8, Cluster::clusters('Child 2.1')->getLeft());
        $this->assertEquals(9, Cluster::clusters('Child 2.1')->getRight());
    }

    public function testMakeLastChildOf()
    {
        Cluster::clusters('Child 1')->makeLastChildOf(Cluster::clusters('Child 3'));

        $this->assertEquals(Cluster::clusters('Child 3'), Cluster::clusters('Child 1')->parent()->first());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testMakeLastChildOfAppendsAtTheEnd()
    {
        $newChild = Cluster::create(['name' => 'Child 4']);

        $newChild->makeLastChildOf(Cluster::clusters('Root 1'));

        $lastChild = Cluster::clusters('Root 1')->children()->get()->last();
        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    public function testMakeLastChildOfMovesWithSubtree()
    {
        Cluster::clusters('Child 2')->makeLastChildOf(Cluster::clusters('Child 1'));

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals(Cluster::clusters('Child 1')->getKey(), Cluster::clusters('Child 2')->getParentId());

        $this->assertEquals(3, Cluster::clusters('Child 2')->getLeft());
        $this->assertEquals(6, Cluster::clusters('Child 2')->getRight());

        $this->assertEquals(2, Cluster::clusters('Child 1')->getLeft());
        $this->assertEquals(7, Cluster::clusters('Child 1')->getRight());
    }

    public function testMakeLastChildOfSwappingRoots()
    {
        $newRoot = Cluster::create(['name' => 'Root 3']);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        Cluster::clusters('Root 2')->makeLastChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Cluster::clusters('Root 2')->getParentId());

        $this->assertEquals(12, Cluster::clusters('Root 2')->getLeft());
        $this->assertEquals(13, Cluster::clusters('Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    public function testMakeLastChildOfSwappingRootsWithSubtrees()
    {
        $newRoot = Cluster::create(['name' => 'Root 3']);

        Cluster::clusters('Root 1')->makeLastChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Cluster::clusters('Root 1')->getParentId());

        $this->assertEquals(4, Cluster::clusters('Root 1')->getLeft());
        $this->assertEquals(13, Cluster::clusters('Root 1')->getRight());

        $this->assertEquals(8, Cluster::clusters('Child 2.1')->getLeft());
        $this->assertEquals(9, Cluster::clusters('Child 2.1')->getRight());
    }

    /**
     * @expectedException Baum\Exceptions\MoveNotPossibleException
     */
    public function testUnpersistedNodeCannotBeMoved()
    {
        $unpersisted = new Cluster(['name' => 'Unpersisted']);

        try {
            $unpersisted->moveToRightOf(Cluster::clusters('Root 1'));
        } catch (\Exception $error) {
            $this->assertInstanceOf(\Baum\Exceptions\MoveNotPossibleException::class, $error);
        }
    }

    /**
     * @expectedException Baum\Exceptions\MoveNotPossibleException
     */
    public function testUnpersistedNodeCannotBeMadeChild()
    {
        $unpersisted = new Cluster(['name' => 'Unpersisted']);

        try {
            $unpersisted->makeChildOf(Cluster::clusters('Root 1'));
        } catch (\Exception $error) {
            $this->assertInstanceOf(\Baum\Exceptions\MoveNotPossibleException::class, $error);
        }
    }

    /**
     * @expectedException Baum\Exceptions\MoveNotPossibleException
     */
    public function testNodesCannotBeMovedToItself()
    {
        $node = Cluster::clusters('Child 1');

        try {
            $node->moveToRightOf($node);
        } catch (\Exception $error) {
            $this->assertInstanceOf(\Baum\Exceptions\MoveNotPossibleException::class, $error);
        }
    }

    /**
     * @expectedException Baum\Exceptions\MoveNotPossibleException
     */
    public function testNodesCannotBeMadeChildOfThemselves()
    {
        $node = Cluster::clusters('Child 1');

        try {
            $node->makeChildOf($node);
        } catch (\Exception $error) {
            $this->assertInstanceOf(\Baum\Exceptions\MoveNotPossibleException::class, $error);
        }
    }

    /**
     * @expectedException Baum\Exceptions\MoveNotPossibleException
     */
    public function testNodesCannotBeMovedToDescendantsOfThemselves()
    {
        $node = Cluster::clusters('Root 1');

        try {
            $node->makeChildOf(Cluster::clusters('Child 2.1'));
        } catch (\Exception $error) {
            $this->assertInstanceOf(\Baum\Exceptions\MoveNotPossibleException::class, $error);
        }
    }

    public function testDepthIsUpdatedWhenMadeChild()
    {
        $a = Cluster::create(['name' => 'A']);
        $b = Cluster::create(['name' => 'B']);
        $c = Cluster::create(['name' => 'C']);
        $d = Cluster::create(['name' => 'D']);

        // a > b > c > d
        $b->makeChildOf($a);
        $c->makeChildOf($b);
        $d->makeChildOf($c);

        $a->reload();
        $b->reload();
        $c->reload();
        $d->reload();

        $this->assertEquals(0, $a->getDepth());
        $this->assertEquals(1, $b->getDepth());
        $this->assertEquals(2, $c->getDepth());
        $this->assertEquals(3, $d->getDepth());
    }

    public function testDepthIsUpdatedOnDescendantsWhenParentMoves()
    {
        $a = Cluster::create(['name' => 'A']);
        $b = Cluster::create(['name' => 'B']);
        $c = Cluster::create(['name' => 'C']);
        $d = Cluster::create(['name' => 'D']);

        // a > b > c > d
        $b->makeChildOf($a);
        $c->makeChildOf($b);
        $d->makeChildOf($c);

        $a->reload();
        $b->reload();
        $c->reload();
        $d->reload();

        $b->moveToRightOf($a);

        $a->reload();
        $b->reload();
        $c->reload();
        $d->reload();

        $this->assertEquals(0, $b->getDepth());
        $this->assertEquals(1, $c->getDepth());
        $this->assertEquals(2, $d->getDepth());
    }
}

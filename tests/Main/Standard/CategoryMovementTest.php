<?php

namespace Baum\Tests\Main\Standard;

use Baum\Exceptions\MoveNotPossibleException;
use Baum\Tests\Main\Concerns\NodeModelExtensionsTestTrait;
use Baum\Tests\Main\Models\Category;
use Baum\Tests\Main\Support\PopulateData;
use Baum\Tests\Main\UnitAbstract;

class CategoryMovementTest extends UnitAbstract
{
    use NodeModelExtensionsTestTrait;

    public function testMoveLeft()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $this->categories('A3')->moveLeft();

        $this->assertNull($this->categories('A3')->getLeftSibling());
        $this->assertEquals($this->categories('A2'), $this->categories('A3')->getRightSibling());
        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMoveLeftRaisesAnExceptionWhenNotPossible()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $node = $this->categories('A3');

        $this->expectException(MoveNotPossibleException::class);
        $node->moveLeft();
        $node->moveLeft();
    }

    public function testMoveLeftDoesNotChangeDepth()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->categories('B3')->moveLeft();

        $this->assertEquals(1, $this->categories('B2')->getDepth());
        $this->assertEquals(1, $this->categories('B3')->getDepth());
        $this->assertEquals(2, $this->categories('B2.1')->getDepth());
    }

    public function testMoveLeftWithSubtree()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $node = $this->categories('B3');
        $node->children()->create(['name' => 'B3.1']);
        $node->children()->create(['name' => 'B3.2']);
        $node->children()->create(['name' => 'B3.3']);

        $this->categories('B3')->moveLeft();

        $this->assertNull($this->categories('B3')->getLeftSibling());
        $this->assertEquals($this->categories('B2'), $this->categories('B3')->getRightSibling());
        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals(0, $this->categories('A1')->getDepth());
        $this->assertEquals(0, $this->categories('B1')->getDepth());

        $this->assertEquals(1, $this->categories('B2')->getDepth());
        $this->assertEquals(1, $this->categories('B3')->getDepth());

        $this->assertEquals(2, $this->categories('B3.1')->getDepth());
        $this->assertEquals(2, $this->categories('B3.2')->getDepth());
        $this->assertEquals(2, $this->categories('B3.3')->getDepth());
    }

    public function testMoveToLeftOf()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->categories('B2.3')->moveToLeftOf($this->categories('B2.1'));

        $this->assertNull($this->categories('B2.3')->getLeftSibling());
        $this->assertEquals($this->categories('B2.1'), $this->categories('B2.3')->getRightSibling());
        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMoveToLeftOfRaisesAnExceptionWhenNotPossible()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->expectException(MoveNotPossibleException::class);
        $this->categories('B2.1')->moveToLeftOf($this->categories('B2.1')->getLeftSibling());
    }

    public function testMoveToLeftOfDoesNotChangeDepth()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->categories('B2.3')->moveToLeftOf($this->categories('B2.1'));

        $this->assertEquals(1, $this->categories('B2')->getDepth());
        $this->assertEquals(2, $this->categories('B2.1')->getDepth());
        $this->assertEquals(2, $this->categories('B2.2')->getDepth());
        $this->assertEquals(2, $this->categories('B2.3')->getDepth());
    }

    public function testMoveToLeftOfWithSubtree()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $node = $this->categories('B3');
        $node->children()->create(['name' => 'B3.1']);
        $node->children()->create(['name' => 'B3.2']);
        $node->children()->create(['name' => 'B3.3']);

        $this->categories('B3')->moveToLeftOf($this->categories('B2'));

        $this->assertNull($this->categories('B3')->getLeftSibling());
        $this->assertEquals($this->categories('B2'), $this->categories('B3')->getRightSibling());
        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals(0, $this->categories('A1')->getDepth());
        $this->assertEquals(0, $this->categories('B1')->getDepth());

        $this->assertEquals(1, $this->categories('B2')->getDepth());
        $this->assertEquals(1, $this->categories('B3')->getDepth());

        $this->assertEquals(2, $this->categories('B3.1')->getDepth());
        $this->assertEquals(2, $this->categories('B3.2')->getDepth());
        $this->assertEquals(2, $this->categories('B3.3')->getDepth());
    }

    public function testMoveRight()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->categories('A2')->moveRight();

        $this->assertNull($this->categories('A2')->getRightSibling());
        $this->assertEquals($this->categories('A3'), $this->categories('A2')->getLeftSibling());
        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMoveRightRaisesAnExceptionWhenNotPossible()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->expectException(MoveNotPossibleException::class);

        $node = $this->categories('A2');

        $node->moveRight();
        $node->moveRight();
    }

    public function testMoveRightDoesNotChangeDepth()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->categories('B2')->moveRight();

        $this->assertEquals(1, $this->categories('B2')->getDepth());
        $this->assertEquals(2, $this->categories('B2.1')->getDepth());
        $this->assertEquals(2, $this->categories('B2.2')->getDepth());
        $this->assertEquals(2, $this->categories('B2.3')->getDepth());
    }

    public function testMoveRightWithSubtree()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->categories('B2')->moveRight();

        $this->assertNull($this->categories('B2')->getRightSibling());
        $this->assertEquals($this->categories('B3'), $this->categories('B2')->getLeftSibling());
        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals(0, $this->categories('A1')->getDepth());
        $this->assertEquals(0, $this->categories('B1')->getDepth());

        $this->assertEquals(1, $this->categories('B2')->getDepth());
        $this->assertEquals(1, $this->categories('B3')->getDepth());

        $this->assertEquals(2, $this->categories('B2.1')->getDepth());
        $this->assertEquals(2, $this->categories('B2.2')->getDepth());
        $this->assertEquals(2, $this->categories('B2.3')->getDepth());
    }

    public function testMoveToRightOf()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->categories('A1')->moveToRightOf($this->categories('B1'));

        $this->assertNull($this->categories('A1')->getRightSibling());

        $this->assertEquals($this->categories('B1'), $this->categories('A1')->getLeftSibling());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMoveToRightOfRaisesAnExceptionWhenNotPossible()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->expectException(MoveNotPossibleException::class);

        $this->categories('B1')->moveToRightOf($this->categories('B1')->getRightSibling());
    }

    public function testMoveToRightOfDoesNotChangeDepth()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->categories('B2.1')->moveToRightOf($this->categories('B2.3'));

        $this->assertEquals(0, $this->categories('B1')->getDepth());
        $this->assertEquals(1, $this->categories('B2')->getDepth());
        $this->assertEquals(2, $this->categories('B2.1')->getDepth());
        $this->assertEquals(2, $this->categories('B2.2')->getDepth());
        $this->assertEquals(2, $this->categories('B2.3')->getDepth());
    }

    public function testMoveToRightOfWithSubtree()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->categories('A1')->moveToRightOf($this->categories('B1'));

        $this->assertNull($this->categories('A1')->getRightSibling());
        $this->assertEquals($this->categories('B1'), $this->categories('A1')->getLeftSibling());
        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals(0, $this->categories('A1')->getDepth());
        $this->assertEquals(0, $this->categories('B1')->getDepth());

        $this->assertEquals(1, $this->categories('A2')->getDepth());
        $this->assertEquals(1, $this->categories('A3')->getDepth());
        $this->assertEquals(1, $this->categories('B2')->getDepth());
        $this->assertEquals(1, $this->categories('B3')->getDepth());

        $this->assertEquals(2, $this->categories('B2.1')->getDepth());
        $this->assertEquals(2, $this->categories('B2.2')->getDepth());
        $this->assertEquals(2, $this->categories('B2.3')->getDepth());
    }

    public function testMakeRoot()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->categories('B2')->makeRoot();

        $newRoot = $this->categories('B2');

        $this->assertNull($newRoot->parent()->first());
        $this->assertEquals(0, $newRoot->getLevel());
        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(18, $newRoot->getRight());

        $this->assertEquals(1, $this->categories('B2.1')->getLevel());
        $this->assertEquals(1, $this->categories('B2.2')->getLevel());
        $this->assertEquals(1, $this->categories('B2.3')->getLevel());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testNullifyParentColumnMakesItRoot()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $node = $this->categories('B2');

        $node->parent_id = null;

        $node->save();

        $this->assertNull($node->parent()->first());
        $this->assertEquals(0, $node->getLevel());
        $this->assertEquals(11, $node->getLeft());
        $this->assertEquals(18, $node->getRight());

        $this->assertEquals(1, $this->categories('B2.1')->getLevel());
        $this->assertEquals(1, $this->categories('B2.2')->getLevel());
        $this->assertEquals(1, $this->categories('B2.3')->getLevel());

//         $this->assertNull($node->parent()->first());
//         $this->assertEquals(0, $node->getLevel());
//         $this->assertEquals(9, $node->getLeft());
//         $this->assertEquals(12, $node->getRight());
//
//         $this->assertEquals(1, $this->categories('Child 2.1')->getLevel());
//
//         $this->assertTrue(Category::isValidNestedSet());
    }

    public function testNullifyParentColumnOnNewNodes()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $node = new Category(['name' => 'Root 3']);

        $node->parent_id = null;

        $node->save();

        $node->reload();

        $this->assertNull($node->parent()->first());
        $this->assertEquals(0, $node->getLevel());
        $this->assertEquals(13, $node->getLeft());
        $this->assertEquals(14, $node->getRight());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testNewCategoryWithNullParent()
    {
        $node = new Category(['name' => 'Root 3']);
        $this->assertTrue($node->isRoot());

        $node->save();
        $this->assertTrue($node->isRoot());

        $node->makeRoot();
        $this->assertTrue($node->isRoot());
    }

    public function testMakeChildOf()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $this->categories('B3')->makeChildOf($this->categories('B2'));

        $this->assertEquals($this->categories('B2'), $this->categories('B3')->parent()->first());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeChildOfAppendsAtTheEnd()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $newChild = Category::create(['name' => 'Child 4']);
        $newChild->makeChildOf($this->categories('B1'));
        $lastChild = $this->categories('B1')->children()->get()->last();

        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeChildOfMovesWithSubtree()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->categories('B1')->makeChildOf($this->categories('A3'));

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($this->categories('A3')->getKey(), $this->categories('B1')->getParentId());

        $this->assertEquals(4, $this->categories('A3')->getLeft());
        $this->assertEquals(17, $this->categories('A3')->getRight());

        $this->assertEquals(5, $this->categories('B1')->getLeft());
        $this->assertEquals(16, $this->categories('B1')->getRight());
    }

    public function testMakeChildOfSwappingRoots()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $newRoot = Category::create(['name' => 'Root 3']);

        $this->assertEquals(19, $newRoot->getLeft());
        $this->assertEquals(20, $newRoot->getRight());

        $this->categories('B1')->makeChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('B1')->getParentId());

        $this->assertEquals(8, $this->categories('B1')->getLeft());
        $this->assertEquals(19, $this->categories('B1')->getRight());

        $this->assertEquals(7, $newRoot->getLeft());
        $this->assertEquals(20, $newRoot->getRight());
    }

    public function testMakeChildOfSwappingRootsWithSubtrees()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $newRoot = Category::create(['name' => 'Root 3']);

        $this->categories('B2')->makeChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('B2')->getParentId());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(20, $newRoot->getRight());

        $this->assertEquals(12, $this->categories('B2')->getLeft());
        $this->assertEquals(19, $this->categories('B2')->getRight());

        $this->assertEquals(13, $this->categories('B2.1')->getLeft());
        $this->assertEquals(14, $this->categories('B2.1')->getRight());

        $this->assertEquals(15, $this->categories('B2.2')->getLeft());
        $this->assertEquals(16, $this->categories('B2.2')->getRight());

        $this->assertEquals(17, $this->categories('B2.3')->getLeft());
        $this->assertEquals(18, $this->categories('B2.3')->getRight());
    }

    public function testMakeFirstChildOf()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $this->categories('B3')->makeFirstChildOf($this->categories('A1'));

        $this->assertEquals($this->categories('A1'), $this->categories('B3')->parent()->first());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeFirstChildOfAppendsAtTheBeginning()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $newChild = Category::create(['name' => 'Child 4']);

        $newChild->makeFirstChildOf($this->categories('A1'));

        $lastChild = $this->categories('A1')->children()->get()->first();
        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeFirstChildOfMovesWithSubtree()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->categories('B2.2')->makeFirstChildOf($this->categories('A1'));

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($this->categories('A1')->getKey(), $this->categories('B2.2')->getParentId());

        $this->assertEquals(1, $this->categories('A1')->getLeft());
        $this->assertEquals(8, $this->categories('A1')->getRight());

        $this->assertEquals(2, $this->categories('B2.2')->getLeft());
        $this->assertEquals(3, $this->categories('B2.2')->getRight());
    }

    public function testMakeFirstChildOfSwappingRoots()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $newRoot = Category::create(['name' => 'Root 3']);

        $this->assertEquals(19, $newRoot->getLeft());
        $this->assertEquals(20, $newRoot->getRight());

        $this->categories('B1')->makeFirstChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('B1')->getParentId());

        $this->assertEquals(8, $this->categories('B1')->getLeft());
        $this->assertEquals(19, $this->categories('B1')->getRight());

        $this->assertEquals(7, $newRoot->getLeft());
        $this->assertEquals(20, $newRoot->getRight());
    }

    public function testMakeFirstChildOfSwappingRootsWithSubtrees()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $newRoot = Category::create(['name' => 'Root 3']);

        $this->categories('B1')->makeFirstChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('B1')->getParentId());

        $this->assertEquals(8, $this->categories('B1')->getLeft());
        $this->assertEquals(19, $this->categories('B1')->getRight());

        $this->assertEquals(10, $this->categories('B2.1')->getLeft());
        $this->assertEquals(11, $this->categories('B2.1')->getRight());

        $this->assertEquals(12, $this->categories('B2.2')->getLeft());
        $this->assertEquals(13, $this->categories('B2.2')->getRight());

        $this->assertEquals(14, $this->categories('B2.3')->getLeft());
        $this->assertEquals(15, $this->categories('B2.3')->getRight());
    }

    public function testMakeLastChildOf()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->categories('B3')->makeLastChildOf($this->categories('A1'));

        $this->assertEquals($this->categories('A1'), $this->categories('B3')->parent()->first());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeLastChildOfAppendsAtTheEnd()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $newChild = Category::create(['name' => 'Child 4']);

        $newChild->makeLastChildOf($this->categories('A1'));

        $lastChild = $this->categories('A1')->children()->get()->last();
        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeLastChildOfMovesWithSubtree()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->categories('B3')->makeLastChildOf($this->categories('B2'));

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($this->categories('B2')->getKey(), $this->categories('B3')->getParentId());

        $this->assertEquals(9, $this->categories('B2.1')->getLeft());
        $this->assertEquals(10, $this->categories('B2.1')->getRight());

        $this->assertEquals(11, $this->categories('B2.2')->getLeft());
        $this->assertEquals(12, $this->categories('B2.2')->getRight());

        $this->assertEquals(13, $this->categories('B2.3')->getLeft());
        $this->assertEquals(14, $this->categories('B2.3')->getRight());

        $this->assertEquals(15, $this->categories('B3')->getLeft());
        $this->assertEquals(16, $this->categories('B3')->getRight());
    }

    public function testMakeLastChildOfSwappingRoots()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $newRoot = Category::create(['name' => 'Root 3']);

        $this->assertEquals(19, $newRoot->getLeft());
        $this->assertEquals(20, $newRoot->getRight());

        $this->categories('B1')->makeLastChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('B1')->getParentId());

        $this->assertEquals(8, $this->categories('B1')->getLeft());
        $this->assertEquals(19, $this->categories('B1')->getRight());

        $this->assertEquals(7, $newRoot->getLeft());
        $this->assertEquals(20, $newRoot->getRight());
    }

    public function testMakeLastChildOfSwappingRootsWithSubtrees()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $newRoot = Category::create(['name' => 'Root 3']);

        $this->categories('A1')->makeLastChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('A1')->getParentId());

        $this->assertEquals(14, $this->categories('A1')->getLeft());
        $this->assertEquals(19, $this->categories('A1')->getRight());

        $this->assertEquals(3, $this->categories('B2.1')->getLeft());
        $this->assertEquals(4, $this->categories('B2.1')->getRight());

        $this->assertEquals(5, $this->categories('B2.2')->getLeft());
        $this->assertEquals(6, $this->categories('B2.2')->getRight());

        $this->assertEquals(7, $this->categories('B2.3')->getLeft());
        $this->assertEquals(8, $this->categories('B2.3')->getRight());
    }

    /**
     * expectedException Baum\Exceptions\MoveNotPossibleException
     *         $this->expectException(MoveNotPossibleException::class);.
     */
    public function testUnpersistedNodeCannotBeMoved()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $unpersisted = new Category(['name' => 'Unpersisted']);
        $this->expectException(MoveNotPossibleException::class);
        $unpersisted->moveToRightOf($this->categories('A1'));
    }

    /**
     * expectedException Baum\Exceptions\MoveNotPossibleException.
     */
    public function testUnpersistedNodeCannotBeMadeChild()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $unpersisted = new Category(['name' => 'Unpersisted']);
        $this->expectException(MoveNotPossibleException::class);
        $unpersisted->makeChildOf($this->categories('A1'));
    }

    /**
     * expectedException Baum\Exceptions\MoveNotPossibleException.
     */
    public function testNodesCannotBeMovedToItself()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $node = $this->categories('A2');
        $this->expectException(MoveNotPossibleException::class);
        $node->moveToRightOf($node);
    }

    /**
     * expectedException Baum\Exceptions\MoveNotPossibleException.
     */
    public function testNodesCannotBeMadeChildOfThemselves()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $node = $this->categories('A1');
        $this->expectException(MoveNotPossibleException::class);
        $node->makeChildOf($node);
    }

    /**
     * expectedException Baum\Exceptions\MoveNotPossibleException.
     */
    public function testNodesCannotBeMovedToDescendantsOfThemselves()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $node = $this->categories('A1');
        $this->expectException(MoveNotPossibleException::class);
        $node->makeChildOf($this->categories('A2'));
    }

    public function testDepthIsUpdatedWhenMadeChild()
    {
        $a = Category::create(['name' => 'A']);
        $b = Category::create(['name' => 'B']);
        $c = Category::create(['name' => 'C']);
        $d = Category::create(['name' => 'D']);

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
        $a = Category::create(['name' => 'A']);
        $b = Category::create(['name' => 'B']);
        $c = Category::create(['name' => 'C']);
        $d = Category::create(['name' => 'D']);

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

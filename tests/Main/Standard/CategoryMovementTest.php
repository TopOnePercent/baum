<?php

namespace Baum\Tests\Main\Standard;

namespace Baum\Tests\Main\Standard;

use Baum\Seeder\CategorySeeder;

use Baum\Tests\Main\Models\Category;
use Baum\Tests\Main\Models\ScopedCategory;
use Baum\Tests\Main\Models\MultiScopedCategory;
use Baum\Tests\Main\Models\OrderedCategory;
use Baum\Tests\Main\Models\OrderedScopedCategory;
use Baum\Tests\Main\Models\SoftCategory;

use Baum\Tests\Main\Models\Cluster;
use Baum\Tests\Main\Models\ScopedCluster;
use Baum\Tests\Main\Models\OrderedCluster;
use Baum\Tests\Main\Models\SoftCluster;

use Baum\Tests\Main\Support\PopulateData;
use Baum\Tests\Main\UnitAbstract;
use Baum\Tests\Main\Concerns\NodeModelExtensionsTest;
use Baum\Exceptions\MoveNotPossibleException;

class CategoryMovementTest extends CategoryAbstract
{

	use NodeModelExtensionsTest;
	
    public function testMoveLeft()
    {

		$build = Category::buildTree(PopulateData::basicTree());

        $this->categories('A3')->moveLeft();

        $this->assertNull($this->categories('A3')->getLeftSibling());
        $this->assertEquals($this->categories('A2'), $this->categories('A3')->getRightSibling());
        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     */
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

    /**
     */
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

    /**
     */
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

    /**
     */
    public function testMoveToRightOfRaisesAnExceptionWhenNotPossible()
    {
		$build = Category::buildTree(PopulateData::deepTree());

		$this->expectException(MoveNotPossibleException::class);

        $this->categories('B1')->moveToRightOf($this->categories('B1')->getRightSibling());
    }

    public function testMoveToRightOfDoesNotChangeDepth()
    {
		$build = Category::buildTree(PopulateData::deepTree());

        $this->categories('B2.1')->moveToRightOf($this->categories('B2.1'));

        $this->assertEquals(1, $this->categories('B1')->getDepth());
        $this->assertEquals(2, $this->categories('B2.1')->getDepth());
        $this->assertEquals(2, $this->categories('B2.2')->getDepth());
        $this->assertEquals(2, $this->categories('B2.3')->getDepth());
    }

    public function testMoveToRightOfWithSubtree()
    {
        $this->categories('Root 1')->moveToRightOf($this->categories('Root 2'));

        $this->assertNull($this->categories('Root 1')->getRightSibling());
        $this->assertEquals($this->categories('Root 2'), $this->categories('Root 1')->getLeftSibling());
        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals(0, $this->categories('Root 1')->getDepth());
        $this->assertEquals(0, $this->categories('Root 2')->getDepth());

        $this->assertEquals(1, $this->categories('Child 1')->getDepth());
        $this->assertEquals(1, $this->categories('Child 2')->getDepth());
        $this->assertEquals(1, $this->categories('Child 3')->getDepth());

        $this->assertEquals(2, $this->categories('Child 2.1')->getDepth());
    }

    public function testMakeRoot()
    {
        $this->categories('Child 2')->makeRoot();

        $newRoot = $this->categories('Child 2');

        $this->assertNull($newRoot->parent()->first());
        $this->assertEquals(0, $newRoot->getLevel());
        $this->assertEquals(9, $newRoot->getLeft());
        $this->assertEquals(12, $newRoot->getRight());

        $this->assertEquals(1, $this->categories('Child 2.1')->getLevel());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testNullifyParentColumnMakesItRoot()
    {
        $node = $this->categories('Child 2');

        $node->parent_id = null;

        $node->save();

        $this->assertNull($node->parent()->first());
        $this->assertEquals(0, $node->getLevel());
        $this->assertEquals(9, $node->getLeft());
        $this->assertEquals(12, $node->getRight());

        $this->assertEquals(1, $this->categories('Child 2.1')->getLevel());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testNullifyParentColumnOnNewNodes()
    {
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
        $this->categories('Child 1')->makeChildOf($this->categories('Child 3'));

        $this->assertEquals($this->categories('Child 3'), $this->categories('Child 1')->parent()->first());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeChildOfAppendsAtTheEnd()
    {
        $newChild = Category::create(['name' => 'Child 4']);
        $newChild->makeChildOf($this->categories('Root 1'));
        $lastChild = $this->categories('Root 1')->children()->get()->last();

        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeChildOfMovesWithSubtree()
    {
        $this->categories('Child 2')->makeChildOf($this->categories('Child 1'));

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($this->categories('Child 1')->getKey(), $this->categories('Child 2')->getParentId());

        $this->assertEquals(3, $this->categories('Child 2')->getLeft());
        $this->assertEquals(6, $this->categories('Child 2')->getRight());

        $this->assertEquals(2, $this->categories('Child 1')->getLeft());
        $this->assertEquals(7, $this->categories('Child 1')->getRight());
    }

    public function testMakeChildOfSwappingRoots()
    {
        $newRoot = Category::create(['name' => 'Root 3']);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        $this->categories('Root 2')->makeChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('Root 2')->getParentId());

        $this->assertEquals(12, $this->categories('Root 2')->getLeft());
        $this->assertEquals(13, $this->categories('Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    public function testMakeChildOfSwappingRootsWithSubtrees()
    {
        $newRoot = Category::create(['name' => 'Root 3']);

        $this->categories('Root 1')->makeChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('Root 1')->getParentId());

        $this->assertEquals(4, $this->categories('Root 1')->getLeft());
        $this->assertEquals(13, $this->categories('Root 1')->getRight());

        $this->assertEquals(8, $this->categories('Child 2.1')->getLeft());
        $this->assertEquals(9, $this->categories('Child 2.1')->getRight());
    }

    public function testMakeFirstChildOf()
    {
        $this->categories('Child 1')->makeFirstChildOf($this->categories('Child 3'));

        $this->assertEquals($this->categories('Child 3'), $this->categories('Child 1')->parent()->first());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeFirstChildOfAppendsAtTheBeginning()
    {
        $newChild = Category::create(['name' => 'Child 4']);

        $newChild->makeFirstChildOf($this->categories('Root 1'));

        $lastChild = $this->categories('Root 1')->children()->get()->first();
        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeFirstChildOfMovesWithSubtree()
    {
        $this->categories('Child 2')->makeFirstChildOf($this->categories('Child 1'));

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($this->categories('Child 1')->getKey(), $this->categories('Child 2')->getParentId());

        $this->assertEquals(3, $this->categories('Child 2')->getLeft());
        $this->assertEquals(6, $this->categories('Child 2')->getRight());

        $this->assertEquals(2, $this->categories('Child 1')->getLeft());
        $this->assertEquals(7, $this->categories('Child 1')->getRight());
    }

    public function testMakeFirstChildOfSwappingRoots()
    {
        $newRoot = Category::create(['name' => 'Root 3']);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        $this->categories('Root 2')->makeFirstChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('Root 2')->getParentId());

        $this->assertEquals(12, $this->categories('Root 2')->getLeft());
        $this->assertEquals(13, $this->categories('Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    public function testMakeFirstChildOfSwappingRootsWithSubtrees()
    {
        $newRoot = Category::create(['name' => 'Root 3']);

        $this->categories('Root 1')->makeFirstChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('Root 1')->getParentId());

        $this->assertEquals(4, $this->categories('Root 1')->getLeft());
        $this->assertEquals(13, $this->categories('Root 1')->getRight());

        $this->assertEquals(8, $this->categories('Child 2.1')->getLeft());
        $this->assertEquals(9, $this->categories('Child 2.1')->getRight());
    }

    public function testMakeLastChildOf()
    {
        $this->categories('Child 1')->makeLastChildOf($this->categories('Child 3'));

        $this->assertEquals($this->categories('Child 3'), $this->categories('Child 1')->parent()->first());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeLastChildOfAppendsAtTheEnd()
    {
        $newChild = Category::create(['name' => 'Child 4']);

        $newChild->makeLastChildOf($this->categories('Root 1'));

        $lastChild = $this->categories('Root 1')->children()->get()->last();
        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());

        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testMakeLastChildOfMovesWithSubtree()
    {
        $this->categories('Child 2')->makeLastChildOf($this->categories('Child 1'));

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($this->categories('Child 1')->getKey(), $this->categories('Child 2')->getParentId());

        $this->assertEquals(3, $this->categories('Child 2')->getLeft());
        $this->assertEquals(6, $this->categories('Child 2')->getRight());

        $this->assertEquals(2, $this->categories('Child 1')->getLeft());
        $this->assertEquals(7, $this->categories('Child 1')->getRight());
    }

    public function testMakeLastChildOfSwappingRoots()
    {
        $newRoot = Category::create(['name' => 'Root 3']);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        $this->categories('Root 2')->makeLastChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('Root 2')->getParentId());

        $this->assertEquals(12, $this->categories('Root 2')->getLeft());
        $this->assertEquals(13, $this->categories('Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    public function testMakeLastChildOfSwappingRootsWithSubtrees()
    {
        $newRoot = Category::create(['name' => 'Root 3']);

        $this->categories('Root 1')->makeLastChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), $this->categories('Root 1')->getParentId());

        $this->assertEquals(4, $this->categories('Root 1')->getLeft());
        $this->assertEquals(13, $this->categories('Root 1')->getRight());

        $this->assertEquals(8, $this->categories('Child 2.1')->getLeft());
        $this->assertEquals(9, $this->categories('Child 2.1')->getRight());
    }

    /**
     * @expectedException Baum\Exceptions\MoveNotPossibleException
     */
    public function testUnpersistedNodeCannotBeMoved()
    {
        $unpersisted = new Category(['name' => 'Unpersisted']);

        $unpersisted->moveToRightOf($this->categories('Root 1'));
    }

    /**
     * @expectedException Baum\Exceptions\MoveNotPossibleException
     */
    public function testUnpersistedNodeCannotBeMadeChild()
    {
        $unpersisted = new Category(['name' => 'Unpersisted']);

        $unpersisted->makeChildOf($this->categories('Root 1'));
    }

    /**
     * @expectedException Baum\Exceptions\MoveNotPossibleException
     */
    public function testNodesCannotBeMovedToItself()
    {
        $node = $this->categories('Child 1');

        $node->moveToRightOf($node);
    }

    /**
     * @expectedException Baum\Exceptions\MoveNotPossibleException
     */
    public function testNodesCannotBeMadeChildOfThemselves()
    {
        $node = $this->categories('Child 1');

        $node->makeChildOf($node);
    }

    /**
     * @expectedException Baum\Exceptions\MoveNotPossibleException
     */
    public function testNodesCannotBeMovedToDescendantsOfThemselves()
    {
        $node = $this->categories('Root 1');

        $node->makeChildOf($this->categories('Child 2.1'));
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

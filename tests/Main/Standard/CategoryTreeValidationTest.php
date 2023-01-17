<?php

namespace Baum\Tests\Main\Standard;

use Baum\Tests\Main\Models\Category;

class CategoryTreeValidationTest extends CategoryAbstract
{
    protected function setUp(): void
    {
        parent::setUp();

        $root_1 = Category::create(['name' => 'Root 1']);

        $child_1 = Category::create(['name' => 'Child 1']);
        $child_1->makeChildOf($root_1);

        $child_2 = Category::create(['name' => 'Child 2']);
        $child_2->makeChildOf($root_1);
        $child_2_1 = Category::create(['name' => 'Child 2.1']);
        $child_2_1->makeChildOf($child_2);

        $child_3 = Category::create(['name' => 'Child 3']);
        $child_3->makeChildOf($root_1);

        $root_2 = Category::create(['name' => 'Root 2']);
    }

    public function testTreeIsNotValidWithNullLefts()
    {
        $this->assertTrue(Category::isValidNestedSet());

        Category::query()->update(['lft' => null]);
        $this->assertFalse(Category::isValidNestedSet());
    }

    public function testTreeIsNotValidWithNullRights()
    {
        $this->assertTrue(Category::isValidNestedSet());

        Category::query()->update(['rgt' => null]);
        $this->assertFalse(Category::isValidNestedSet());
    }

    public function testTreeIsNotValidWhenRightsEqualLefts()
    {
        $this->assertTrue(Category::isValidNestedSet());

        $child2 = Category::categories('Child 2');
        $child2->rgt = $child2->lft;
        $child2->save();

        $this->assertFalse(Category::isValidNestedSet());
    }

    public function testTreeIsNotValidWhenLeftEqualsParent()
    {
        $this->assertTrue(Category::isValidNestedSet());

        $child2 = Category::categories('Child 2');
        $child2->lft = Category::categories('Root 1')->getLeft();
        $child2->save();

        $this->assertFalse(Category::isValidNestedSet());
    }

    public function testTreeIsNotValidWhenRightEqualsParent()
    {
        $this->assertTrue(Category::isValidNestedSet());

        $child2 = Category::categories('Child 2');
        $child2->rgt = Category::categories('Root 1')->getRight();
        $child2->save();

        $this->assertFalse(Category::isValidNestedSet());
    }

    public function testTreeIsValidWithMissingMiddleNode()
    {
        $this->assertTrue(Category::isValidNestedSet());

        Category::query()->delete(Category::categories('Child 2')->getKey());
        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testTreeIsNotValidWithOverlappingRoots()
    {
        $this->assertTrue(Category::isValidNestedSet());

        // Force Root 2 to overlap with Root 1
        $root = Category::categories('Root 2');
        $root->lft = 0;
        $root->save();

        $this->assertFalse(Category::isValidNestedSet());
    }

    public function testNodeDeletionDoesNotMakeTreeInvalid()
    {
        $this->assertTrue(Category::isValidNestedSet());

        Category::categories('Root 2')->delete();
        $this->assertTrue(Category::isValidNestedSet());

        Category::categories('Child 1')->delete();
        $this->assertTrue(Category::isValidNestedSet());
    }

    public function testNodeDeletionWithSubtreeDoesNotMakeTreeInvalid()
    {
        $this->assertTrue(Category::isValidNestedSet());

        Category::categories('Child 2')->delete();
        $this->assertTrue(Category::isValidNestedSet());
    }
}

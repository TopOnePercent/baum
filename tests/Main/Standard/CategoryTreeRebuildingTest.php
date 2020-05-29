<?php

namespace Baum\Tests\Main\Standard;

use Baum\Tests\Main\Models\Category;

class CategoryTreeRebuildingTest extends CategoryAbstract
{
    public function testRebuild()
    {
        $this->assertTrue(Category::isValidNestedSet());

        $root = Category::root();
        Category::query()->update(['lft' => null, 'rgt' => null]);
        $this->assertFalse(Category::isValidNestedSet());

        Category::rebuild();
        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($root, Category::root());
    }

    public function testRebuildPresevesRootNodes()
    {
        $root1 = Category::create(['name' => 'Test Root 1']);
        $root2 = Category::create(['name' => 'Test Root 2']);
        $root3 = Category::create(['name' => 'Test Root 3']);

        $root2->makeChildOf($root1);
        $root3->makeChildOf($root1);

        $lastRoot = Category::roots()->reOrderBy($root1->getLeftColumnName(), 'desc')->first();

        Category::query()->update(['lft' => null, 'rgt' => null]);
        Category::rebuild();

        $this->assertEquals($lastRoot, Category::roots()->reOrderBy($root1->getLeftColumnName(), 'desc')->first());
    }

    public function testRebuildRecomputesDepth()
    {
        $this->assertTrue(Category::isValidNestedSet());

        Category::query()->update(['lft' => null, 'rgt' => null, 'depth' => 0]);
        $this->assertFalse(Category::isValidNestedSet());

        Category::rebuild();

        $expected = [0, 1, 1, 2, 1, 0];
        $this->assertEquals($expected, Category::all()->map(function ($n) {
            return $n->getDepth();
        })->all());
    }

    public function testRebuildWithScope()
    {
        MultiScopedCategory::query()->delete();

        $root = MultiScopedCategory::create(['name' => 'A', 'company_id' => 721, 'language' => 'es']);
        $child1 = MultiScopedCategory::create(['name' => 'A.1', 'company_id' => 721, 'language' => 'es']);
        $child2 = MultiscopedCategory::create(['name' => 'A.2', 'company_id' => 721, 'language' => 'es']);

        $child1->makeChildOf($root);
        $child2->makeChildOf($root);

        MultiscopedCategory::query()->update(['lft' => null, 'rgt' => null]);
        $this->assertFalse(MultiscopedCategory::isValidNestedSet());

        MultiscopedCategory::rebuild();
        $this->assertTrue(MultiscopedCategory::isValidNestedSet());

        $this->assertEquals($root->getAttributes(), $this->categories('A', 'MultiScopedCategory')->getAttributes());

        // Compare attributes, not objects
        $expected = array_map(function ($item) {
            return $item->getAttributes();
        }, [$child1, $child2]);

        $children = $this->categories('A', 'MultiScopedCategory')->children()->get()->all();
        $children = array_map(function ($item) {
            return $item->getAttributes();
        }, $children);

        $this->assertEquals($expected, $children);
    }

    public function testRebuildWithMultipleScopes()
    {
        MultiScopedCategory::query()->delete();

        $root1 = MultiScopedCategory::create(['name' => 'TL1', 'company_id' => 1, 'language' => 'en']);
        $child11 = MultiScopedCategory::create(['name' => 'C11', 'company_id' => 1, 'language' => 'en']);
        $child12 = MultiScopedCategory::create(['name' => 'C12', 'company_id' => 1, 'language' => 'en']);
        $child11->makeChildOf($root1);
        $child12->makeChildOf($root1);

        $root2 = MultiScopedCategory::create(['name' => 'TL2', 'company_id' => 2, 'language' => 'en']);
        $child21 = MultiScopedCategory::create(['name' => 'C21', 'company_id' => 2, 'language' => 'en']);
        $child22 = MultiScopedCategory::create(['name' => 'C22', 'company_id' => 2, 'language' => 'en']);
        $child21->makeChildOf($root2);
        $child22->makeChildOf($root2);

        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $tree = MultiScopedCategory::query()->orderBy($root1->getKeyName())->get()->all();

        MultiScopedCategory::query()->update(['lft' => null, 'rgt' => null]);
        MultiScopedCategory::rebuild();

        $this->assertTrue(MultiScopedCategory::isValidNestedSet());
        $this->assertEquals($tree, MultiScopedCategory::query()->orderBy($root1->getKeyName())->get()->all());
    }
}

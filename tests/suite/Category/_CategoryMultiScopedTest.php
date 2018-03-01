<?php

class CategoryScopedTest extends BaumTestCase
{
    public function setUp()
    {
        parent::setUp();

        with(new CategoryMigrator())->up();
        with(new ScopedCategorySeeder())->run();
    }

    public function testSimpleMovements()
    {
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $root3 = ScopedCategory::create(['name' => 'Root 3', 'company_id' => 2]);
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $this->categories('Child 6', 'ScopedCategory')->makeChildOf($root3);
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $root3->reload();
        $expected = [$this->categories('Child 6', 'ScopedCategory')];
        $this->assertEquals($expected, $root3->children()->get()->all());
    }

    public function testSimpleSubtreeMovements()
    {
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $root3 = ScopedCategory::create(['name' => 'Root 3', 'company_id' => 2]);
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $this->categories('Child 5', 'ScopedCategory')->makeChildOf($root3);
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $root3->reload();
        $expected = [
            $this->categories('Child 5', 'ScopedCategory'),
            $this->categories('Child 5.1', 'ScopedCategory'),
        ];

        $this->assertEquals($expected, $root3->getDescendants()->all());
    }

    public function testFullSubtreeMovements()
    {
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $root3 = ScopedCategory::create(['name' => 'Root 3', 'company_id' => 2]);
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $this->categories('Root 2', 'ScopedCategory')->makeChildOf($root3);
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $root3->reload();
        $expected = [
            $this->categories('Root 2', 'ScopedCategory'),
            $this->categories('Child 4', 'ScopedCategory'),
            $this->categories('Child 5', 'ScopedCategory'),
            $this->categories('Child 5.1', 'ScopedCategory'),
            $this->categories('Child 6', 'ScopedCategory'),
        ];

        $this->assertEquals($expected, $root3->getDescendants()->all());
    }
}

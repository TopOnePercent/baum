<?php

namespace Baum\Tests\Basic;

use Baum\Tests\Basic\Models\BasicBaum;

class BasicBaumTest extends UnitAbstract
{
    /** @test */
    public function no_records_test()
    {
        //factory(BasicBaum::class, 50)->create();
        $this->assertEquals(BasicBaum::count(), 0);
    }

    /** @test */
    public function root_test()
    {
        $root = BasicBaum::create(['name' => 'Root category']);
        $this->assertEquals(BasicBaum::count(), 1);
    }

    /** @test */
    public function parent_child_test()
    {
        $parent = BasicBaum::create(['name' => 'Alpha']);
        $child1 = $parent->children()->create(['name' => 'Bravo']);
        $child2 = $parent->children()->create(['name' => 'Charlie']);

        $this->assertTrue($parent->isRoot());
        $this->assertFalse($child1->isRoot());
        $this->assertFalse($child2->isRoot());

        $this->assertTrue($parent->isLeaf());
        $this->assertTrue($child1->isLeaf());
        $this->assertTrue($child2->isLeaf());

        $this->assertFalse($parent->isChildOf($child1));
        $this->assertFalse($parent->isChildOf($child2));
        $this->assertTrue($child1->isChildOf($parent));
        $this->assertTrue($child2->isChildOf($parent));
    }
}

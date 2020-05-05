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
        $parent = factory(BasicBaum::class)->create();

        $child1 = factory(BasicBaum::class)->create();
        $child1->makeChildOf($parent);

        $child2 = $parent->children()->create(factory(BasicBaum::class)->raw());

        $other = factory(BasicBaum::class, 3)->states('root')->create();

        $this->assertTrue($parent->isRoot());
        $this->assertFalse($child1->isRoot());
        $this->assertFalse($child2->isRoot());

        $this->assertFalse($parent->isLeaf());
        $this->assertTrue($child1->isLeaf());
        $this->assertTrue($child2->isLeaf());

        $this->assertFalse($parent->isChildOf($child1));
        $this->assertFalse($parent->isChildOf($child2));
        $this->assertTrue($child1->isChildOf($parent));
        $this->assertTrue($child2->isChildOf($parent));

        // print_r(BasicBaum::getNestedList('name'));
    }

    /** @test */
    public function root_count_test()
    {
        $rand = rand(2, 5);
        factory(BasicBaum::class, $rand)->states('root')->create();
        $this->assertEquals($rand, BasicBaum::roots()->count());
    }

    /** @test */
    public function get_root_test()
    {
        $root = factory(BasicBaum::class)->create();
        $child1 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child2 = $child1->children()->create(factory(BasicBaum::class)->raw());
        $child3 = $child2->children()->create(factory(BasicBaum::class)->raw());
        $child4 = $child3->children()->create(factory(BasicBaum::class)->raw());
        $child5 = $child4->children()->create(factory(BasicBaum::class)->raw());

        $this->assertEquals($root->name, $child1->getRoot()->name);
        $this->assertEquals($root->name, $child2->getRoot()->name);
        $this->assertEquals($root->name, $child3->getRoot()->name);
        $this->assertEquals($root->name, $child4->getRoot()->name);
        $this->assertEquals($root->name, $child5->getRoot()->name);
        $this->assertEquals($root->name, $root->getRoot()->name);

        //  		$rand = rand(2,5);
//  		factory(BasicBaum::class, $rand)->states('root')->create();
//  		$this->assertEquals($rand, BasicBaum::roots()->count());
    }

    /** @test */
    public function getAncestorsAndSelfTest()
    {
        $root = factory(BasicBaum::class)->create();
        $child1 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child2 = $child1->children()->create(factory(BasicBaum::class)->raw());
        $child3 = $child2->children()->create(factory(BasicBaum::class)->raw());
        $child4 = $child3->children()->create(factory(BasicBaum::class)->raw());
        $child5 = $child4->children()->create(factory(BasicBaum::class)->raw());

        $this->assertEquals(1, $root->getAncestorsAndSelf()->count());
        $this->assertEquals(2, $child1->getAncestorsAndSelf()->count());
        $this->assertEquals(3, $child2->getAncestorsAndSelf()->count());
        $this->assertEquals(4, $child3->getAncestorsAndSelf()->count());
        $this->assertEquals(5, $child4->getAncestorsAndSelf()->count());
        $this->assertEquals(6, $child5->getAncestorsAndSelf()->count());
    }

    /** @test */
    public function getAncestorsAndSelfWithoutRootTest()
    {
        $root = factory(BasicBaum::class)->create();
        $child1 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child2 = $child1->children()->create(factory(BasicBaum::class)->raw());
        $child3 = $child2->children()->create(factory(BasicBaum::class)->raw());
        $child4 = $child3->children()->create(factory(BasicBaum::class)->raw());
        $child5 = $child4->children()->create(factory(BasicBaum::class)->raw());

        $this->assertEquals(0, $root->getAncestorsAndSelfWithoutRoot()->count());
        $this->assertEquals(1, $child1->getAncestorsAndSelfWithoutRoot()->count());
        $this->assertEquals(2, $child2->getAncestorsAndSelfWithoutRoot()->count());
        $this->assertEquals(3, $child3->getAncestorsAndSelfWithoutRoot()->count());
        $this->assertEquals(4, $child4->getAncestorsAndSelfWithoutRoot()->count());
        $this->assertEquals(5, $child5->getAncestorsAndSelfWithoutRoot()->count());
    }

    /** @test */
    public function getAncestorsTest()
    {
        $root = factory(BasicBaum::class)->create();
        $child1 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child2 = $child1->children()->create(factory(BasicBaum::class)->raw());
        $child3 = $child2->children()->create(factory(BasicBaum::class)->raw());
        $child4 = $child3->children()->create(factory(BasicBaum::class)->raw());
        $child5 = $child4->children()->create(factory(BasicBaum::class)->raw());

        $this->assertEquals(0, $root->getAncestors()->count());
        $this->assertEquals(1, $child1->getAncestors()->count());
        $this->assertEquals(2, $child2->getAncestors()->count());
        $this->assertEquals(3, $child3->getAncestors()->count());
        $this->assertEquals(4, $child4->getAncestors()->count());
        $this->assertEquals(5, $child5->getAncestors()->count());
    }

    /** @test */
    public function getAncestorsWithoutRootTest()
    {
        $root = factory(BasicBaum::class)->create();
        $child1 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child2 = $child1->children()->create(factory(BasicBaum::class)->raw());
        $child3 = $child2->children()->create(factory(BasicBaum::class)->raw());
        $child4 = $child3->children()->create(factory(BasicBaum::class)->raw());
        $child5 = $child4->children()->create(factory(BasicBaum::class)->raw());

        $this->assertEquals(0, $root->getAncestorsWithoutRoot()->count());
        $this->assertEquals(0, $child1->getAncestorsWithoutRoot()->count());
        $this->assertEquals(1, $child2->getAncestorsWithoutRoot()->count());
        $this->assertEquals(2, $child3->getAncestorsWithoutRoot()->count());
        $this->assertEquals(3, $child4->getAncestorsWithoutRoot()->count());
        $this->assertEquals(4, $child5->getAncestorsWithoutRoot()->count());
    }

    /** @test */
    public function getSiblingsAndSelfTest()
    {
        $root = factory(BasicBaum::class)->create();
        $child1 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child2 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child3 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child4 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child5 = $child4->children()->create(factory(BasicBaum::class)->raw());

        $this->assertEquals(1, $root->getSiblingsAndSelf()->count());
        $this->assertEquals(4, $child1->getSiblingsAndSelf()->count());
        $this->assertEquals(4, $child2->getSiblingsAndSelf()->count());
        $this->assertEquals(4, $child3->getSiblingsAndSelf()->count());
        $this->assertEquals(4, $child4->getSiblingsAndSelf()->count());
        $this->assertEquals(1, $child5->getSiblingsAndSelf()->count());
    }

    /** @test */
    public function getSiblingsTest()
    {
        $root = factory(BasicBaum::class)->create();
        $child1 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child2 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child3 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child4 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child5 = $child4->children()->create(factory(BasicBaum::class)->raw());

        $this->assertEquals(0, $root->getSiblings()->count());
        $this->assertEquals(3, $child1->getSiblings()->count());
        $this->assertEquals(3, $child2->getSiblings()->count());
        $this->assertEquals(3, $child3->getSiblings()->count());
        $this->assertEquals(3, $child4->getSiblings()->count());
        $this->assertEquals(0, $child5->getSiblings()->count());
    }

    /** @test */
    public function getLeavesTest()
    {
        $root = factory(BasicBaum::class)->create();
        $child1 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child2 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child3 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child4 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child5 = $root->children()->create(factory(BasicBaum::class)->raw());

        $data = BasicBaum::first();

        $this->assertEquals(5, $data->getLeaves()->count());
    }

    /** @test */
    public function getDescendantsAndSelfTest()
    {
        $root = factory(BasicBaum::class)->create();
        $child1 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child2 = $child1->children()->create(factory(BasicBaum::class)->raw());
        $child3 = $child1->children()->create(factory(BasicBaum::class)->raw());
        $child4 = $child2->children()->create(factory(BasicBaum::class)->raw());
        $child5 = $child3->children()->create(factory(BasicBaum::class)->raw());

        $data = BasicBaum::first();

        $this->assertEquals(6, $data->getDescendantsAndSelf()->count());
    }

    /** @test */
    public function getDescendantsTest()
    {
        $root = factory(BasicBaum::class)->create();
        $child1 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child2 = $child1->children()->create(factory(BasicBaum::class)->raw());
        $child3 = $child1->children()->create(factory(BasicBaum::class)->raw());
        $child4 = $child2->children()->create(factory(BasicBaum::class)->raw());
        $child5 = $child3->children()->create(factory(BasicBaum::class)->raw());

        $data = BasicBaum::first();

        $this->assertEquals(5, $data->getDescendants()->count());
    }

    /** @test */
    public function getImmediateDescendantsTest()
    {
        $root = factory(BasicBaum::class)->create();
        $child1 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child2 = $root->children()->create(factory(BasicBaum::class)->raw());
        $child3 = $child1->children()->create(factory(BasicBaum::class)->raw());
        $child4 = $child2->children()->create(factory(BasicBaum::class)->raw());
        $child5 = $root->children()->create(factory(BasicBaum::class)->raw());

        $data = BasicBaum::first();

        $this->assertEquals(3, $data->getImmediateDescendants()->count());
    }
}

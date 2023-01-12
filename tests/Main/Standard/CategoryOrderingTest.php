<?php

namespace Baum\Tests\Main\Standard;
use Baum\Tests\Main\Models\OrderedCategory;

class CategoryOrderingTest extends CategoryAbstract
{
    protected function setUp(): void
    {
        parent::setUp();

        $root_z = OrderedCategory::create(['name' => 'Root Z']);

        $child_c = OrderedCategory::create(['name' => 'Child C']);
        $child_c->makeChildOf($root_z);

        $child_g = OrderedCategory::create(['name' => 'Child G']);
        $child_g->makeChildOf($root_z);

        $child_g_1 = OrderedCategory::create(['name' => 'Child G.1']);
        $child_g_1->makeChildOf($child_g);

        $child_a = OrderedCategory::create(['name' => 'Child A']);
        $child_a->makeChildOf($root_z);

        $root_a = OrderedCategory::create(['name' => 'Root A']);
    }

    public function testAllStaticWithCustomOrder()
    {
        $results = OrderedCategory::all();
        $expected = OrderedCategory::query()->orderBy('name')->get();

        $this->assertEquals($results, $expected);
    }

    public function testToHierarchyNestsCorrectlyWithOrder()
    {
        $expectedWhole = [
            'Root A' => null,
            'Root Z' => [
                'Child A' => null,
                'Child C' => null,
                'Child G' => [
                    'Child G.1' => null,
                ],
            ],
        ];

        $this->assertArraysAreEqual(
            $expectedWhole,
            OrderedCategory::hmap(OrderedCategory::all()->toHierarchy()->toArray())
        );

        $expectedSubtreeZ = [
            'Root Z' => [
                'Child A' => null,
                'Child C' => null,
                'Child G' => [
                    'Child G.1' => null,
                ],
            ],
        ];

        $this->assertArraysAreEqual(
            $expectedSubtreeZ,
            OrderedCategory::hmap(OrderedCategory::categories('Root Z')
                ->getDescendantsAndSelf()
                ->toHierarchy()
                ->toArray()
            )
        );
    }

    public function testRootsStaticWithCustomOrder()
    {
        $category = OrderedCategory::create(['name' => 'A new root is born']);
        $category->syncOriginal(); // ¿? --> This should be done already !?

        $roots = OrderedCategory::roots()->get();

        $this->assertCount(3, $roots);
        $this->assertEquals($category->getAttributes(), $roots->first()->getAttributes());
    }

    public function testChildrenRelationObeysCustomOrdering()
    {
        $children = OrderedCategory::find(1)->children()->get()->all();

        $expected = [OrderedCategory::find(5), OrderedCategory::find(2), OrderedCategory::find(3)];
        $this->assertEquals($expected, $children);
    }
}

<?php

namespace Baum\Tests\Suite\Category;

class CategoryOrderingTest extends CategoryAbstract
{
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
            hmap(OrderedCategory::all()->toHierarchy()->toArray())
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
            hmap($this->categories('Root Z', 'OrderedCategory')
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

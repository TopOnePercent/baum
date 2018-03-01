<?php

class OrderedClusterHeirarchyTestCase extends OrderedClusterTestCase
{
    public function testAllStaticWithCustomOrder()
    {
        $results = OrderedCluster::all();
        $expected = OrderedCluster::query()->orderBy('name')->get();

        $this->assertEquals($results, $expected);
    }

    public function testRootsStaticWithCustomOrder()
    {
        $cluster = OrderedCluster::create(['name' => 'A new root is born']);
        $cluster->syncOriginal(); // ¿? --> This should be done already !?

        $roots = OrderedCluster::roots()->get();

        $this->assertCount(3, $roots);
        $this->assertEquals($cluster->getAttributes(), $roots->first()->getAttributes());
    }

    public function testToHierarchyNestsCorrectlyWithOrder()
    {
        $expectedWhole = [
            'Root A' => null,
            'Root Z' => [
                'Child A' => null,
                'Child C' => null,
                'Child G' => ['Child G.1' => null],
            ],
        ];

        $current = OrderedCluster::all()->toHierarchy()->toArray();

        $this->assertArraysAreEqual($expectedWhole, hmap(OrderedCluster::all()->toHierarchy()->toArray()));

        $expectedSubtreeZ = [
            'Root Z' => [
                'Child A' => null,
                'Child C' => null,
                'Child G' => ['Child G.1' => null],
            ],
        ];

        $this->assertArraysAreEqual($expectedSubtreeZ, hmap($this->clusters('Root Z', 'OrderedCluster')->getDescendantsAndSelf()->toHierarchy()->toArray()));
    }
}

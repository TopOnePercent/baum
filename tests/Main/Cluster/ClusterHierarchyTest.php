<?php

use Baum\Tests\Main\UnitAbstract;
use Baum\Tests\Main\Models\Cluster;

class ClusterHierarchyTest extends UnitAbstract
{
    protected function setUp(): void
    {
        parent::setUp();

        $root_1 = Cluster::create(['name' => 'Root 1']);

        $child_1 = Cluster::create(['name' => 'Child 1']);
        $child_1->makeChildOf($root_1);

        $child_2 = Cluster::create(['name' => 'Child 2']);
        $child_2->makeChildOf($root_1);
        $child_2_1 = Cluster::create(['name' => 'Child 2.1']);
        $child_2_1->makeChildOf($child_2);

        $child_3 = Cluster::create(['name' => 'Child 3']);
        $child_3->makeChildOf($root_1);

        $root_2 = Cluster::create(['name' => 'Root 2']);
    }

    protected function nestUptoAt(Cluster $parent, int $count)
    {
        for ($i = 0; $i < $count; $i++){
            $child = Cluster::create(['name' => $parent->name . '.1']);
            $child->makeChildOf($parent);
            $parent = $child;
        }
    }

    public function testAllStatic()
    {
        $results = Cluster::all();
        $expected = Cluster::query()->orderBy('lft')->get();

        $this->assertEquals($results, $expected);
    }

    public function testRootsStatic()
    {
        $query = Cluster::whereNull('parent_id')->get();

        $roots = Cluster::roots()->get();

        $this->assertEquals($query->count(), $roots->count());
        $this->assertCount(2, $roots);

        foreach ($query->pluck('id') as $node) {
            $this->assertContains($node, $roots->pluck('id'));
        }
    }

    public function testRootStatic()
    {
        $this->assertEquals(Cluster::root(), Cluster::clusters('Root 1'));
    }

    public function testAllLeavesStatic()
    {
        $allLeaves = Cluster::allLeaves()->get();

        $this->assertCount(4, $allLeaves);

        $leaves = $allLeaves->pluck('name');

        $this->assertContains('Child 1', $leaves);
        $this->assertContains('Child 2.1', $leaves);
        $this->assertContains('Child 3', $leaves);
        $this->assertContains('Root 2', $leaves);
    }

    public function testAllTrunksStatic()
    {
        $allTrunks = Cluster::allTrunks()->get();

        $this->assertCount(1, $allTrunks);

        $trunks = $allTrunks->pluck('name');
        $this->assertContains('Child 2', $trunks);
    }

    public function testGetRoot()
    {
        $this->assertEquals(Cluster::clusters('Root 1'), Cluster::clusters('Root 1')->getRoot());
        $this->assertEquals(Cluster::clusters('Root 2'), Cluster::clusters('Root 2')->getRoot());

        $this->assertEquals(Cluster::clusters('Root 1'), Cluster::clusters('Child 1')->getRoot());
        $this->assertEquals(Cluster::clusters('Root 1'), Cluster::clusters('Child 2')->getRoot());
        $this->assertEquals(Cluster::clusters('Root 1'), Cluster::clusters('Child 2.1')->getRoot());
        $this->assertEquals(Cluster::clusters('Root 1'), Cluster::clusters('Child 3')->getRoot());
    }

    public function testGetRootEqualsSelfIfUnpersisted()
    {
        $cluster = new Cluster();

        $this->assertEquals($cluster->getRoot(), $cluster);
    }

    public function testGetRootEqualsValueIfUnpersisted()
    {
        $parent = Cluster::roots()->first();

        $child = new Cluster();
        $child->setAttribute('id', $parent->getKey());

        $this->assertEquals($child->getRoot()->id, $parent->id);
    }

    public function testIsRoot()
    {
        $this->assertTrue(Cluster::clusters('Root 1')->isRoot());
        $this->assertTrue(Cluster::clusters('Root 2')->isRoot());

        $this->assertFalse(Cluster::clusters('Child 1')->isRoot());
        $this->assertFalse(Cluster::clusters('Child 2')->isRoot());
        $this->assertFalse(Cluster::clusters('Child 2.1')->isRoot());
        $this->assertFalse(Cluster::clusters('Child 3')->isRoot());
    }

    public function testGetLeaves()
    {
        $leaves = [Cluster::clusters('Child 1'), Cluster::clusters('Child 2.1'), Cluster::clusters('Child 3')];

        $this->assertEquals($leaves, Cluster::clusters('Root 1')->getLeaves()->all());
    }

    public function testGetLeavesInIteration()
    {
        $node = Cluster::clusters('Root 1');

        $expectedNames = [
            'Child 1',
            'Child 2.1',
            'Child 3',
        ];

        foreach ($node->getLeaves() as $i => $leaf) {
            $this->assertEquals($expectedNames[$i], $leaf->name);
        }
    }

    public function testGetTrunks()
    {
        $trunks = [Cluster::clusters('Child 2')];

        $this->assertEquals($trunks, Cluster::clusters('Root 1')->getTrunks()->all());
    }

    public function testGetTrunksInIteration()
    {
        $node = Cluster::clusters('Root 1');

        $expectedNames = ['Child 2'];

        foreach ($node->getTrunks() as $i => $trunk) {
            $this->assertEquals($expectedNames[$i], $trunk->name);
        }
    }

    public function testIsLeaf()
    {
        $this->assertTrue(Cluster::clusters('Child 1')->isLeaf());
        $this->assertTrue(Cluster::clusters('Child 2.1')->isLeaf());
        $this->assertTrue(Cluster::clusters('Child 3')->isLeaf());
        $this->assertTrue(Cluster::clusters('Root 2')->isLeaf());

        $this->assertFalse(Cluster::clusters('Root 1')->isLeaf());
        $this->assertFalse(Cluster::clusters('Child 2')->isLeaf());

        $new = new Cluster();
        $this->assertFalse($new->isLeaf());
    }

    public function testIsTrunk()
    {
        $this->assertFalse(Cluster::clusters('Child 1')->isTrunk());
        $this->assertFalse(Cluster::clusters('Child 2.1')->isTrunk());
        $this->assertFalse(Cluster::clusters('Child 3')->isTrunk());
        $this->assertFalse(Cluster::clusters('Root 2')->isTrunk());

        $this->assertFalse(Cluster::clusters('Root 1')->isTrunk());
        $this->assertTrue(Cluster::clusters('Child 2')->isTrunk());

        $new = new Cluster();
        $this->assertFalse($new->isTrunk());
    }

    public function testWithoutNodeScope()
    {
        $child = Cluster::clusters('Child 2.1');

        $expected = [Cluster::clusters('Root 1'), $child];

        $this->assertEquals($expected, $child->ancestorsAndSelf()->withoutNode(Cluster::clusters('Child 2'))->get()->all());
    }

    public function testWithoutSelfScope()
    {
        $child = Cluster::clusters('Child 2.1');

        $expected = [Cluster::clusters('Root 1'), Cluster::clusters('Child 2')];

        $this->assertEquals($expected, $child->ancestorsAndSelf()->withoutSelf()->get()->all());
    }

    public function testWithoutRootScope()
    {
        $child = Cluster::clusters('Child 2.1');

        $expected = [Cluster::clusters('Child 2'), $child];

        $this->assertEquals($expected, $child->ancestorsAndSelf()->withoutRoot()->get()->all());
    }

    public function testLimitDepthScope()
    {
        $node = Cluster::clusters('Child 2');

        $descendancy = $node->descendants()->pluck('id');

        $this->assertEmpty($node->descendants()->limitDepth(0)->pluck('id'));
        $this->assertEquals($node, $node->descendantsAndSelf()->limitDepth(0)->first());

        $result = $node->descendants()->limitDepth(3)->pluck('id');

        $this->assertEquals(array_slice($descendancy->toArray(), 0, 3), $node->descendants()->limitDepth(3)->pluck('id')->toArray());
        $this->assertEquals(array_slice($descendancy->toArray(), 0, 5), $node->descendants()->limitDepth(5)->pluck('id')->toArray());
        $this->assertEquals(array_slice($descendancy->toArray(), 0, 7), $node->descendants()->limitDepth(7)->pluck('id')->toArray());

        $this->assertEquals($descendancy, $node->descendants()->limitDepth(1000)->pluck('id'));
    }

    public function testGetAncestorsAndSelf()
    {
        $child = Cluster::clusters('Child 2.1');

        $expected = [Cluster::clusters('Root 1'), Cluster::clusters('Child 2'), $child];

        $this->assertEquals($expected, $child->getAncestorsAndSelf()->all());
    }

    public function testGetAncestorsAndSelfWithoutRoot()
    {
        $child = Cluster::clusters('Child 2.1');

        $expected = [Cluster::clusters('Child 2'), $child];

        $this->assertEquals($expected, $child->getAncestorsAndSelfWithoutRoot()->all());
    }

    public function testGetAncestors()
    {
        $child = Cluster::clusters('Child 2.1');

        $expected = [Cluster::clusters('Root 1'), Cluster::clusters('Child 2')];

        $this->assertEquals($expected, $child->getAncestors()->all());
    }

    public function testGetAncestorsWithoutRoot()
    {
        $child = Cluster::clusters('Child 2.1');

        $expected = [Cluster::clusters('Child 2')];

        $this->assertEquals($expected, $child->getAncestorsWithoutRoot()->all());
    }

    public function testGetDescendantsAndSelf()
    {
        $parent = Cluster::clusters('Root 1');

        $expected = [
            $parent,
            Cluster::clusters('Child 1'),
            Cluster::clusters('Child 2'),
            Cluster::clusters('Child 2.1'),
            Cluster::clusters('Child 3'),
        ];

        $this->assertCount(count($expected), $parent->getDescendantsAndSelf());

        $this->assertEquals($expected, $parent->getDescendantsAndSelf()->all());
    }

    public function testGetDescendantsAndSelfWithLimit()
    {
        $this->nestUptoAt(Cluster::clusters('Child 2.1'), 3);

        $parent = Cluster::clusters('Root 1');

        $this->assertEquals([$parent], $parent->getDescendantsAndSelf(0)->all());

        $this->assertEquals([
            $parent,
            Cluster::clusters('Child 1'),
            Cluster::clusters('Child 2'),
            Cluster::clusters('Child 3'),
        ], $parent->getDescendantsAndSelf(1)->all());

        $this->assertEquals([
            $parent,
            Cluster::clusters('Child 1'),
            Cluster::clusters('Child 2'),
            Cluster::clusters('Child 2.1'),
            Cluster::clusters('Child 3'),
        ], $parent->getDescendantsAndSelf(2)->all());

        $this->assertEquals([
            $parent,
            Cluster::clusters('Child 1'),
            Cluster::clusters('Child 2'),
            Cluster::clusters('Child 2.1'),
            Cluster::clusters('Child 2.1.1'),
            Cluster::clusters('Child 3'),
        ], $parent->getDescendantsAndSelf(3)->all());

        $this->assertEquals([
            $parent,
            Cluster::clusters('Child 1'),
            Cluster::clusters('Child 2'),
            Cluster::clusters('Child 2.1'),
            Cluster::clusters('Child 2.1.1'),
            Cluster::clusters('Child 2.1.1.1'),
            Cluster::clusters('Child 3'),
        ], $parent->getDescendantsAndSelf(4)->all());

        $this->assertEquals([
            $parent,
            Cluster::clusters('Child 1'),
            Cluster::clusters('Child 2'),
            Cluster::clusters('Child 2.1'),
            Cluster::clusters('Child 2.1.1'),
            Cluster::clusters('Child 2.1.1.1'),
            Cluster::clusters('Child 2.1.1.1.1'),
            Cluster::clusters('Child 3'),
        ], $parent->getDescendantsAndSelf(10)->all());
    }

    public function testGetDescendants()
    {
        $parent = Cluster::clusters('Root 1');

        $expected = [
            Cluster::clusters('Child 1'),
            Cluster::clusters('Child 2'),
            Cluster::clusters('Child 2.1'),
            Cluster::clusters('Child 3'),
        ];

        $this->assertCount(count($expected), $parent->getDescendants());

        $this->assertEquals($expected, $parent->getDescendants()->all());
    }

    public function testGetDescendantsWithLimit()
    {
        $this->nestUptoAt(Cluster::clusters('Child 2.1'), 3);

        $parent = Cluster::clusters('Root 1');

        $this->assertEmpty($parent->getDescendants(0)->all());

        $this->assertEquals([
            Cluster::clusters('Child 1'),
            Cluster::clusters('Child 2'),
            Cluster::clusters('Child 3'),
        ], $parent->getDescendants(1)->all());

        $this->assertEquals([
            Cluster::clusters('Child 1'),
            Cluster::clusters('Child 2'),
            Cluster::clusters('Child 2.1'),
            Cluster::clusters('Child 3'),
        ], $parent->getDescendants(2)->all());

        $this->assertEquals([
            Cluster::clusters('Child 1'),
            Cluster::clusters('Child 2'),
            Cluster::clusters('Child 2.1'),
            Cluster::clusters('Child 2.1.1'),
            Cluster::clusters('Child 3'),
        ], $parent->getDescendants(3)->all());

        $this->assertEquals([
            Cluster::clusters('Child 1'),
            Cluster::clusters('Child 2'),
            Cluster::clusters('Child 2.1'),
            Cluster::clusters('Child 2.1.1'),
            Cluster::clusters('Child 2.1.1.1'),
            Cluster::clusters('Child 3'),
        ], $parent->getDescendants(4)->all());

        $this->assertEquals([
            Cluster::clusters('Child 1'),
            Cluster::clusters('Child 2'),
            Cluster::clusters('Child 2.1'),
            Cluster::clusters('Child 2.1.1'),
            Cluster::clusters('Child 2.1.1.1'),
            Cluster::clusters('Child 2.1.1.1.1'),
            Cluster::clusters('Child 3'),
        ], $parent->getDescendants(5)->all());

        $this->assertEquals([
            Cluster::clusters('Child 1'),
            Cluster::clusters('Child 2'),
            Cluster::clusters('Child 2.1'),
            Cluster::clusters('Child 2.1.1'),
            Cluster::clusters('Child 2.1.1.1'),
            Cluster::clusters('Child 2.1.1.1.1'),
            Cluster::clusters('Child 3'),
        ], $parent->getDescendants(10)->all());
    }

    public function testDescendantsRecursesChildren()
    {
        $a = Cluster::create(['name' => 'A']);
        $b = Cluster::create(['name' => 'B']);
        $c = Cluster::create(['name' => 'C']);

        // a > b > c
        $b->makeChildOf($a);
        $c->makeChildOf($b);

        $a->reload();
        $b->reload();
        $c->reload();

        $this->assertEquals(1, $a->children()->count());
        $this->assertEquals(1, $b->children()->count());
        $this->assertEquals(2, $a->descendants()->count());
    }

    public function testGetImmediateDescendants()
    {
        $expected = [Cluster::clusters('Child 1'), Cluster::clusters('Child 2'), Cluster::clusters('Child 3')];

        $this->assertEquals($expected, Cluster::clusters('Root 1')->getImmediateDescendants()->all());

        $this->assertEquals([Cluster::clusters('Child 2.1')], Cluster::clusters('Child 2')->getImmediateDescendants()->all());

        $this->assertEmpty(Cluster::clusters('Root 2')->getImmediateDescendants()->all());
    }

    public function testIsSelfOrAncestorOf()
    {
        $this->assertTrue(Cluster::clusters('Root 1')->isSelfOrAncestorOf(Cluster::clusters('Child 1')));
        $this->assertTrue(Cluster::clusters('Root 1')->isSelfOrAncestorOf(Cluster::clusters('Child 2.1')));
        $this->assertTrue(Cluster::clusters('Child 2')->isSelfOrAncestorOf(Cluster::clusters('Child 2.1')));
        $this->assertFalse(Cluster::clusters('Child 2.1')->isSelfOrAncestorOf(Cluster::clusters('Child 2')));
        $this->assertFalse(Cluster::clusters('Child 1')->isSelfOrAncestorOf(Cluster::clusters('Child 2')));
        $this->assertTrue(Cluster::clusters('Child 1')->isSelfOrAncestorOf(Cluster::clusters('Child 1')));
    }

    public function testIsAncestorOf()
    {
        $this->assertTrue(Cluster::clusters('Root 1')->isAncestorOf(Cluster::clusters('Child 1')));
        $this->assertTrue(Cluster::clusters('Root 1')->isAncestorOf(Cluster::clusters('Child 2.1')));
        $this->assertTrue(Cluster::clusters('Child 2')->isAncestorOf(Cluster::clusters('Child 2.1')));
        $this->assertFalse(Cluster::clusters('Child 2.1')->isAncestorOf(Cluster::clusters('Child 2')));
        $this->assertFalse(Cluster::clusters('Child 1')->isAncestorOf(Cluster::clusters('Child 2')));
        $this->assertFalse(Cluster::clusters('Child 1')->isAncestorOf(Cluster::clusters('Child 1')));
    }

    public function testIsChildOf()
    {
        $this->assertTrue(Cluster::clusters('Child 1')->isChildOf(Cluster::clusters('Root 1')));
        $this->assertTrue(Cluster::clusters('Child 2')->isChildOf(Cluster::clusters('Root 1')));
        $this->assertTrue(Cluster::clusters('Child 2.1')->isChildOf(Cluster::clusters('Child 2')));
        $this->assertFalse(Cluster::clusters('Child 2.1')->isChildOf(Cluster::clusters('Root 1')));
        $this->assertFalse(Cluster::clusters('Child 2.1')->isChildOf(Cluster::clusters('Child 1')));
    }

    public function testIsSelfOrDescendantOf()
    {
        $this->assertTrue(Cluster::clusters('Child 1')->isSelfOrDescendantOf(Cluster::clusters('Root 1')));
        $this->assertTrue(Cluster::clusters('Child 2.1')->isSelfOrDescendantOf(Cluster::clusters('Root 1')));
        $this->assertTrue(Cluster::clusters('Child 2.1')->isSelfOrDescendantOf(Cluster::clusters('Child 2')));
        $this->assertFalse(Cluster::clusters('Child 2')->isSelfOrDescendantOf(Cluster::clusters('Child 2.1')));
        $this->assertFalse(Cluster::clusters('Child 2')->isSelfOrDescendantOf(Cluster::clusters('Child 1')));
        $this->assertTrue(Cluster::clusters('Child 1')->isSelfOrDescendantOf(Cluster::clusters('Child 1')));
    }

    public function testIsDescendantOf()
    {
        $this->assertTrue(Cluster::clusters('Child 1')->isDescendantOf(Cluster::clusters('Root 1')));
        $this->assertTrue(Cluster::clusters('Child 2.1')->isDescendantOf(Cluster::clusters('Root 1')));
        $this->assertTrue(Cluster::clusters('Child 2.1')->isDescendantOf(Cluster::clusters('Child 2')));
        $this->assertFalse(Cluster::clusters('Child 2')->isDescendantOf(Cluster::clusters('Child 2.1')));
        $this->assertFalse(Cluster::clusters('Child 2')->isDescendantOf(Cluster::clusters('Child 1')));
        $this->assertFalse(Cluster::clusters('Child 1')->isDescendantOf(Cluster::clusters('Child 1')));
    }

    public function testGetSiblingsAndSelf()
    {
        $child = Cluster::clusters('Child 2');

        $expected = [Cluster::clusters('Child 1'), $child, Cluster::clusters('Child 3')];
        $this->assertEquals($expected, $child->getSiblingsAndSelf()->all());

        $expected = [Cluster::clusters('Root 1'), Cluster::clusters('Root 2')];
        $this->assertEquals($expected, Cluster::clusters('Root 1')->getSiblingsAndSelf()->all());
    }

    public function testGetSiblings()
    {
        $child = Cluster::clusters('Child 2');

        $expected = [Cluster::clusters('Child 1'), Cluster::clusters('Child 3')];

        $this->assertEquals($expected, $child->getSiblings()->all());
    }

    public function testGetLeftSibling()
    {
        $this->assertEquals(Cluster::clusters('Child 1'), Cluster::clusters('Child 2')->getLeftSibling());
        $this->assertEquals(Cluster::clusters('Child 2'), Cluster::clusters('Child 3')->getLeftSibling());
    }

    public function testGetLeftSiblingOfFirstRootIsNull()
    {
        $this->assertNull(Cluster::clusters('Root 1')->getLeftSibling());
    }

    public function testGetLeftSiblingWithNoneIsNull()
    {
        $this->assertNull(Cluster::clusters('Child 2.1')->getLeftSibling());
    }

    public function testGetLeftSiblingOfLeftmostNodeIsNull()
    {
        $this->assertNull(Cluster::clusters('Child 1')->getLeftSibling());
    }

    public function testGetRightSibling()
    {
        $this->assertEquals(Cluster::clusters('Child 3'), Cluster::clusters('Child 2')->getRightSibling());
        $this->assertEquals(Cluster::clusters('Child 2'), Cluster::clusters('Child 1')->getRightSibling());
    }

    public function testGetRightSiblingOfRoots()
    {
        $this->assertEquals(Cluster::clusters('Root 2'), Cluster::clusters('Root 1')->getRightSibling());
        $this->assertNull(Cluster::clusters('Root 2')->getRightSibling());
    }

    public function testGetRightSiblingWithNoneIsNull()
    {
        $this->assertNull(Cluster::clusters('Child 2.1')->getRightSibling());
    }

    public function testGetRightSiblingOfRightmostNodeIsNull()
    {
        $this->assertNull(Cluster::clusters('Child 3')->getRightSibling());
    }

    public function testInsideSubtree()
    {
        $this->assertFalse(Cluster::clusters('Child 1')->insideSubtree(Cluster::clusters('Root 2')));
        $this->assertFalse(Cluster::clusters('Child 2')->insideSubtree(Cluster::clusters('Root 2')));
        $this->assertFalse(Cluster::clusters('Child 3')->insideSubtree(Cluster::clusters('Root 2')));

        $this->assertTrue(Cluster::clusters('Child 1')->insideSubtree(Cluster::clusters('Root 1')));
        $this->assertTrue(Cluster::clusters('Child 2')->insideSubtree(Cluster::clusters('Root 1')));
        $this->assertTrue(Cluster::clusters('Child 2.1')->insideSubtree(Cluster::clusters('Root 1')));
        $this->assertTrue(Cluster::clusters('Child 3')->insideSubtree(Cluster::clusters('Root 1')));

        $this->assertTrue(Cluster::clusters('Child 2.1')->insideSubtree(Cluster::clusters('Child 2')));
        $this->assertFalse(Cluster::clusters('Child 2.1')->insideSubtree(Cluster::clusters('Root 2')));
    }

    public function testGetLevel()
    {
        $this->assertEquals(0, Cluster::clusters('Root 1')->getLevel());
        $this->assertEquals(1, Cluster::clusters('Child 1')->getLevel());
        $this->assertEquals(2, Cluster::clusters('Child 2.1')->getLevel());
    }

    public function testToHierarchyReturnsAnEloquentCollection()
    {
        $categories = Cluster::all()->toHierarchy();

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $categories);
    }

    public function testToHierarchyReturnsHierarchicalData()
    {
        $categories = Cluster::all()->toHierarchy();

        $this->assertEquals(2, $categories->count());

        $first = $categories->first();
        $this->assertEquals('Root 1', $first->name);
        $this->assertEquals(3, $first->children->count());

        $first_lvl2 = $first->children->first();
        $this->assertEquals('Child 1', $first_lvl2->name);
        $this->assertEquals(0, $first_lvl2->children->count());
    }

    public function testToHierarchyNestsCorrectly()
    {
        // Prune all categories
        Cluster::query()->delete();

        // Build a sample tree structure:
        //
        //   - A
        //     |- A.1
        //     |- A.2
        //   - B
        //     |- B.1
        //     |- B.2
        //         |- B.2.1
        //         |- B.2.2
        //           |- B.2.2.1
        //         |- B.2.3
        //     |- B.3
        //   - C
        //     |- C.1
        //     |- C.2
        //   - D
        //
        $a = Cluster::create(['name' => 'A']);
        $b = Cluster::create(['name' => 'B']);
        $c = Cluster::create(['name' => 'C']);
        $d = Cluster::create(['name' => 'D']);

        $ch = Cluster::create(['name' => 'A.1']);
        $ch->makeChildOf($a);

        $ch = Cluster::create(['name' => 'A.2']);
        $ch->makeChildOf($a);

        $ch = Cluster::create(['name' => 'B.1']);
        $ch->makeChildOf($b);

        $ch = Cluster::create(['name' => 'B.2']);
        $ch->makeChildOf($b);

        $ch2 = Cluster::create(['name' => 'B.2.1']);
        $ch2->makeChildOf($ch);

        $ch2 = Cluster::create(['name' => 'B.2.2']);
        $ch2->makeChildOf($ch);

        $ch3 = Cluster::create(['name' => 'B.2.2.1']);
        $ch3->makeChildOf($ch2);

        $ch2 = Cluster::create(['name' => 'B.2.3']);
        $ch2->makeChildOf($ch);

        $ch = Cluster::create(['name' => 'B.3']);
        $ch->makeChildOf($b);

        $ch = Cluster::create(['name' => 'C.1']);
        $ch->makeChildOf($c);

        $ch = Cluster::create(['name' => 'C.2']);
        $ch->makeChildOf($c);

        $this->assertTrue(Cluster::isValidNestedSet());

        // Build expectations (expected trees/subtrees)
        $expectedWholeTree = [
            'A' => ['A.1' => null, 'A.2' => null],
            'B' => [
                'B.1' => null,
                'B.2' => [
                    'B.2.1' => null,
                    'B.2.2' => ['B.2.2.1' => null],
                    'B.2.3' => null,
                ],
                'B.3' => null,
            ],
            'C' => ['C.1' => null, 'C.2' => null],
            'D' => null,
        ];

        $expectedSubtreeA = ['A' =>   ['A.1' => null, 'A.2' => null]];

        $expectedSubtreeB = [
            'B' => [
                'B.1' => null,
                'B.2' => [
                    'B.2.1' => null,
                    'B.2.2' => ['B.2.2.1' => null],
                    'B.2.3' => null,
                ],
                'B.3' => null,
            ],
        ];

        $expectedSubtreeC = ['C.1' => null, 'C.2' => null];

        $expectedSubtreeD = ['D' => null];

        // Perform assertions
        $wholeTree = Cluster::hmap(Cluster::all()->toHierarchy()->toArray());
        $this->assertArraysAreEqual($expectedWholeTree, $wholeTree);

        $subtreeA = Cluster::hmap(Cluster::clusters('A')->getDescendantsAndSelf()->toHierarchy()->toArray());
        $this->assertArraysAreEqual($expectedSubtreeA, $subtreeA);

        $subtreeB = Cluster::hmap(Cluster::clusters('B')->getDescendantsAndSelf()->toHierarchy()->toArray());
        $this->assertArraysAreEqual($expectedSubtreeB, $subtreeB);

        $subtreeC = Cluster::hmap(Cluster::clusters('C')->getDescendants()->toHierarchy()->toArray());
        $this->assertArraysAreEqual($expectedSubtreeC, $subtreeC);

        $subtreeD = Cluster::hmap(Cluster::clusters('D')->getDescendantsAndSelf()->toHierarchy()->toArray());
        $this->assertArraysAreEqual($expectedSubtreeD, $subtreeD);

        $this->assertTrue(Cluster::clusters('D')->getDescendants()->toHierarchy()->isEmpty());
    }

    public function testToHierarchyNestsCorrectlyNotSequential()
    {
        $parent = Cluster::clusters('Child 1');

        $parent->children()->create(['name' => 'Child 1.1']);

        $parent->children()->create(['name' => 'Child 1.2']);

        $this->assertTrue(Cluster::isValidNestedSet());

        $expected = [
            'Child 1' => [
                'Child 1.1' => null,
                'Child 1.2' => null,
            ],
        ];

        $parent->reload();
        $this->assertArraysAreEqual($expected, Cluster::hmap($parent->getDescendantsAndSelf()->toHierarchy()->toArray()));
    }

    public function testGetNestedList()
    {
        $seperator = ' ';
        $nestedList = Cluster::getNestedList('name', 'id', $seperator);

        $expected = [
            Cluster::clusters('Root 1')->id => str_repeat($seperator, 0).'Root 1',
            Cluster::clusters('Child 1')->id => str_repeat($seperator, 1).'Child 1',
            Cluster::clusters('Child 2')->id => str_repeat($seperator, 1).'Child 2',
            Cluster::clusters('Child 2.1')->id => str_repeat($seperator, 2).'Child 2.1',
            Cluster::clusters('Child 3')->id => str_repeat($seperator, 1).'Child 3',
            Cluster::clusters('Root 2')->id => str_repeat($seperator, 0).'Root 2',
        ];

        $this->assertArraysAreEqual($expected, $nestedList);
    }

    public function testGetNestedListSymbol()
    {
        $symbol = '- ';
        $seperator = ' ';
        $nestedList = Cluster::getNestedList('name', 'id', $seperator, $symbol);

        $expected = [
            Cluster::clusters('Root 1')->id => str_repeat($seperator, 0).$symbol.'Root 1',
            Cluster::clusters('Child 1')->id => str_repeat($seperator, 1).$symbol.'Child 1',
            Cluster::clusters('Child 2')->id => str_repeat($seperator, 1).$symbol.'Child 2',
            Cluster::clusters('Child 2.1')->id => str_repeat($seperator, 2).$symbol.'Child 2.1',
            Cluster::clusters('Child 3')->id => str_repeat($seperator, 1).$symbol.'Child 3',
            Cluster::clusters('Root 2')->id => str_repeat($seperator, 0).$symbol.'Root 2',
        ];

        $this->assertArraysAreEqual($expected, $nestedList);
    }
}

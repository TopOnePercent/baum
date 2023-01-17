<?php

namespace Baum\Tests\Main\Standard;

use Baum\Tests\Main\Concerns\NodeModelExtensionsTest;
use Baum\Tests\Main\Models\Category;
use Baum\Tests\Main\Support\MyTrait;
use Baum\Tests\Main\Support\PopulateData;
//use Baum\Tests\Main\Support\Cast;
use Baum\Tests\Main\UnitAbstract;

class CategoryHierarchyTest extends UnitAbstract
{
    use MyTrait;
    use NodeModelExtensionsTest;

    public function testAllStatic()
    {
        $results = Category::all();
        $expected = Category::query()->orderBy('lft')->get();

        $this->assertEquals($results, $expected);
    }

    public function testAllStaticSomeColumns()
    {
        $results = Category::all(['id', 'name'])->toArray();
        $expected = Category::query()->select(['id', 'name'])->orderBy('lft')->get()->toArray();

        $this->assertEquals($results, $expected);
    }

    public function testRootsStatic()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $query = Category::whereNull('parent_id')->get();
        $roots = Category::roots()->get();

        $this->assertEquals($query->count(), $roots->count());
        $this->assertCount(2, $roots);

        foreach ($query->pluck('id') as $node) {
            $this->assertContains($node, $roots->pluck('id'));
        }
    }

    public function testRootStatic()
    {
        $build = Category::buildTree(PopulateData::basicTree());
        $this->assertEquals(Category::root()->name, 'A1');
    }

    public function testAllLeavesStatic()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $allLeaves = Category::allLeaves()->get();

        $this->assertCount(4, $allLeaves);

        $leaves = $allLeaves->pluck('name');

        $this->assertContains('A2', $leaves);
        $this->assertContains('A3', $leaves);
        $this->assertContains('B2', $leaves);
        $this->assertContains('B3', $leaves);
    }

    public function testAllTrunksStatic()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $allTrunks = Category::allTrunks()->get();

        $this->assertCount(1, $allTrunks);

        $trunks = $allTrunks->pluck('name');
        $this->assertContains('B2', $trunks);
    }

    public function testGetRoot()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $this->assertEquals($this->categories('A1'), $this->categories('A1')->getRoot());
        $this->assertEquals($this->categories('A1'), $this->categories('A2')->getRoot());
        $this->assertEquals($this->categories('A1'), $this->categories('A3')->getRoot());

        $this->assertEquals($this->categories('B1'), $this->categories('B1')->getRoot());
        $this->assertEquals($this->categories('B1'), $this->categories('B2')->getRoot());
        $this->assertEquals($this->categories('B1'), $this->categories('B3')->getRoot());
    }

    public function testGetRootEqualsSelfIfUnpersisted()
    {
        $category = new Category();

        $this->assertEquals($category->getRoot(), $category);
    }

    // public function testGetRootEqualsValueIfSetIsUnpersisted()
    // {
    //     $parent = Category::roots()->first();

    //     $child = new Category();
    //     $child->setAttribute($child->getParentColumnName(), $parent->getKey());

    //     $this->assertEquals($child->getRoot(), $parent);
    // }

    public function testIsRoot()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $this->assertTrue($this->categories('A1')->isRoot());
        $this->assertFalse($this->categories('A2')->isRoot());
        $this->assertFalse($this->categories('A3')->isRoot());

        $this->assertTrue($this->categories('B1')->isRoot());
        $this->assertFalse($this->categories('B2')->isRoot());
        $this->assertFalse($this->categories('B3')->isRoot());
    }

    public function testGetLeaves()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $leaves = [$this->categories('A2'), $this->categories('A3')];
        $this->assertEquals($leaves, $this->categories('A1')->getLeaves()->all());

        $leaves = [$this->categories('B2'), $this->categories('B3')];
        $this->assertEquals($leaves, $this->categories('B1')->getLeaves()->all());
    }

    public function testGetLeavesInIteration()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $node = $this->categories('A1');

        $expectedIds = [2, 3];

        foreach ($node->getLeaves() as $i => $leaf) {
            $this->assertEquals($expectedIds[$i], $leaf->getKey());
        }
    }

    public function testGetTrunks()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $trunks = [$this->categories('B2')];

        $this->assertEquals($trunks, $this->categories('B1')->getTrunks()->all());
    }

    public function testGetTrunksInIteration()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $node = $this->categories('B1');

        $expectedIds = [5];

        foreach ($node->getTrunks() as $i => $trunk) {
            $this->assertEquals($expectedIds[$i], $trunk->getKey());
        }
    }

    public function testIsLeaf()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $this->assertTrue($this->categories('A2')->isLeaf());
        $this->assertTrue($this->categories('A3')->isLeaf());
        $this->assertTrue($this->categories('B2')->isLeaf());
        $this->assertTrue($this->categories('B3')->isLeaf());

        $this->assertFalse($this->categories('A1')->isLeaf());
        $this->assertFalse($this->categories('B1')->isLeaf());

        $new = new Category();
        $this->assertFalse($new->isLeaf());
    }

    public function testIsTrunk()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->assertFalse($this->categories('A1')->isTrunk());
        $this->assertFalse($this->categories('A2')->isTrunk());
        $this->assertFalse($this->categories('A3')->isTrunk());
        $this->assertFalse($this->categories('B1')->isTrunk());
        $this->assertTrue($this->categories('B2')->isTrunk());
        $this->assertFalse($this->categories('B3')->isTrunk());
        $this->assertFalse($this->categories('B2.1')->isTrunk());
        $this->assertFalse($this->categories('B2.2')->isTrunk());
        $this->assertFalse($this->categories('B2.3')->isTrunk());

        $new = new Category();
        $this->assertFalse($new->isTrunk());
    }

    public function testWithoutNodeScope()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $child = $this->categories('B2.1');

        $expected = [$this->categories('B1'), $child];

        $this->assertEquals($expected, $child->ancestorsAndSelf()->withoutNode($this->categories('B2'))->get()->all());
    }

    public function testWithoutSelfScope()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $child = $this->categories('B2.2');

        $expected = [$this->categories('B1'), $this->categories('B2')];

        $this->assertEquals($expected, $child->ancestorsAndSelf()->withoutSelf()->get()->all());
    }

    public function testWithoutRootScope()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $child = $this->categories('B2.3');

        $expected = [$this->categories('B2'), $child];

        $this->assertEquals($expected, $child->ancestorsAndSelf()->withoutRoot()->get()->all());
    }

    public function testLimitDepthScope()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $node = $this->categories('B1');

        $this->assertEquals(2, $node->descendants()->limitDepth(1)->count());
        $this->assertEquals(5, $node->descendants()->limitDepth(2)->count());
    }

    public function testGetAncestorsAndSelf()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $child = $this->categories('B2.2');

        $expected = [$this->categories('B1'), $this->categories('B2'), $child];

        $this->assertEquals($expected, $child->getAncestorsAndSelf()->all());
    }

    public function testGetAncestorsAndSelfWithoutRoot()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $child = $this->categories('B2.2');

        $expected = [$this->categories('B2'), $child];

        $this->assertEquals($expected, $child->getAncestorsAndSelfWithoutRoot()->all());
    }

    public function testGetAncestors()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $child = $this->categories('B2.2');

        $expected = [
            $this->categories('B1'),
            $this->categories('B2'),
        ];

        $this->assertEquals($expected, $child->getAncestors()->all());
    }

    public function testGetAncestorsWithoutRoot()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $child = $this->categories('B2.2');

        $expected = [
            $this->categories('B2'),
        ];

        $this->assertEquals($expected, $child->getAncestorsWithoutRoot()->all());
    }

    public function testGetDescendantsAndSelf()
    {
        $build = Category::buildTree(PopulateData::deepTree());
        $parent = $this->categories('B1');
        $expected = [
            $parent,
            $this->categories('B2'),
            $this->categories('B2.1'),
            $this->categories('B2.2'),
            $this->categories('B2.3'),
            $this->categories('B3'),
        ];
        $this->assertCount(count($expected), $parent->getDescendantsAndSelf());
        $this->assertEquals($expected, $parent->getDescendantsAndSelf()->all());
    }

    public function testGetDescendantsAndSelfWithLimit()
    {
        $build = Category::buildTree(PopulateData::deepTree());
        $parent = $this->categories('B1');

        $expected = [
            $parent,
            $this->categories('B2'),
            $this->categories('B2.1'),
            $this->categories('B2.2'),
            $this->categories('B2.3'),
            $this->categories('B3'),
        ];

        $this->assertEquals($expected, $parent->getDescendantsAndSelf(2)->all());

        $expected = [
            $parent,
            $this->categories('B2'),
            $this->categories('B3'),
        ];

        $this->assertEquals($expected, $parent->getDescendantsAndSelf(1)->all());

        $this->assertEquals([$parent], $parent->getDescendantsAndSelf(0)->all());
    }

    public function testGetDescendants()
    {
        $build = Category::buildTree(PopulateData::deepTree());
        $parent = $this->categories('B1');

        $expected = [
            $this->categories('B2'),
            $this->categories('B2.1'),
            $this->categories('B2.2'),
            $this->categories('B2.3'),
            $this->categories('B3'),
        ];

        $this->assertCount(count($expected), $parent->getDescendants());

        $this->assertEquals($expected, $parent->getDescendants()->all());
    }

    public function testGetDescendantsWithLimit()
    {
        $build = Category::buildTree(PopulateData::deepTree());
        $parent = $this->categories('B1');

        $expected = [
            $parent,
            $this->categories('B2'),
            $this->categories('B2.1'),
            $this->categories('B2.2'),
            $this->categories('B2.3'),
            $this->categories('B3'),
        ];

        $this->assertEquals($expected, $parent->getDescendantsAndSelf(2)->all());

        $expected = [
            $parent,
            $this->categories('B2'),
            $this->categories('B3'),
        ];

        $this->assertEquals($expected, $parent->getDescendantsAndSelf(1)->all());

        $this->assertEmpty($parent->getDescendants(0)->all());
    }

    public function testDescendantsRecursesChildren()
    {
        $a = Category::create(['name' => 'A']);
        $b = Category::create(['name' => 'B']);
        $c = Category::create(['name' => 'C']);

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
        $build = Category::buildTree(PopulateData::deepTree());

        $expected = [
            $this->categories('B2'),
            $this->categories('B3'),
        ];

        $this->assertEquals(
            $expected,
            $this->categories('B1')->getImmediateDescendants()->all()
        );

        $expected = [
            $this->categories('B2.1'),
            $this->categories('B2.2'),
            $this->categories('B2.3'),
        ];

        $this->assertEquals(
            $expected,
            $this->categories('B2')->getImmediateDescendants()->all()
        );

        $this->assertEmpty($this->categories('B2.2')->getImmediateDescendants()->all());
    }

    public function testIsSelfOrAncestorOf()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->assertTrue(
            $this->categories('B2.2')->isSelfOrAncestorOf(
                $this->categories('B2.2')
            )
        );

        $this->assertTrue(
            $this->categories('B2')->isSelfOrAncestorOf(
                $this->categories('B2.2')
            )
        );

        $this->assertTrue(
            $this->categories('B2')->isSelfOrAncestorOf(
                $this->categories('B2.2')
            )
        );

        $this->assertFalse(
            $this->categories('A1')->isSelfOrAncestorOf(
                $this->categories('B2.2')
            )
        );

        $this->assertTrue(
            $this->categories('B2')->isSelfOrAncestorOf(
                $this->categories('B2')
            )
        );

        $this->assertTrue(
            $this->categories('B1')->isSelfOrAncestorOf(
                $this->categories('B2')
            )
        );

        $this->assertFalse(
            $this->categories('A1')->isSelfOrAncestorOf(
                $this->categories('B2')
            )
        );

        $this->assertTrue(
            $this->categories('B1')->isSelfOrAncestorOf(
                $this->categories('B1')
            )
        );

        $this->assertFalse(
            $this->categories('A1')->isSelfOrAncestorOf(
                $this->categories('B1')
            )
        );
    }

    public function testIsAncestorOf()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->assertTrue(
            $this->categories('B1')->isAncestorOf(
                $this->categories('B2.1')
            )
        );

        $this->assertTrue(
            $this->categories('B1')->isAncestorOf(
                $this->categories('B2.2')
            )
        );

        $this->assertTrue(
            $this->categories('B1')->isAncestorOf(
                $this->categories('B2.3')
            )
        );

        $this->assertTrue(
            $this->categories('B2')->isAncestorOf(
                $this->categories('B2.1')
            )
        );

        $this->assertTrue(
            $this->categories('B2')->isAncestorOf(
                $this->categories('B2.2')
            )
        );

        $this->assertTrue(
            $this->categories('B2')->isAncestorOf(
                $this->categories('B2.3')
            )
        );

        $this->assertFalse(
            $this->categories('B3')->isAncestorOf(
                $this->categories('B2.1')
            )
        );

        $this->assertFalse(
            $this->categories('B3')->isAncestorOf(
                $this->categories('B2.2')
            )
        );

        $this->assertFalse(
            $this->categories('B3')->isAncestorOf(
                $this->categories('B2.3')
            )
        );
    }

    public function testIsSelfOrDescendantOf()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->assertTrue(
            $this->categories('B2.2')->isSelfOrDescendantOf(
                $this->categories('B1')
            )
        );

        $this->assertTrue(
            $this->categories('B2')->isSelfOrDescendantOf(
                $this->categories('B1')
            )
        );

        $this->assertTrue(
            $this->categories('B1')->isSelfOrDescendantOf(
                $this->categories('B1')
            )
        );

        $this->assertFalse(
            $this->categories('B2.2')->isSelfOrDescendantOf(
                $this->categories('A1')
            )
        );

        $this->assertFalse(
            $this->categories('B2')->isSelfOrDescendantOf(
                $this->categories('A2')
            )
        );

        $this->assertFalse(
            $this->categories('B1')->isSelfOrDescendantOf(
                $this->categories('A3')
            )
        );
    }

    public function testIsDescendantOf()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->assertTrue(
            $this->categories('B2.2')->isDescendantOf(
                $this->categories('B1')
            )
        );

        $this->assertTrue(
            $this->categories('B2')->isDescendantOf(
                $this->categories('B1')
            )
        );

        $this->assertFalse(
            $this->categories('B1')->isDescendantOf(
                $this->categories('B1')
            )
        );

        $this->assertFalse(
            $this->categories('B2.2')->isDescendantOf(
                $this->categories('A1')
            )
        );

        $this->assertFalse(
            $this->categories('B2')->isDescendantOf(
                $this->categories('A2')
            )
        );

        $this->assertFalse(
            $this->categories('B1')->isDescendantOf(
                $this->categories('A3')
            )
        );
    }

    public function testGetSiblingsAndSelf()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $expected = [
            $this->categories('A1'),
            $this->categories('B1'),
        ];

        $this->assertEquals(
            $expected,
            $this->categories('A1')->getSiblingsAndSelf()->all()
        );

        $this->assertEquals(
            $expected,
            $this->categories('B1')->getSiblingsAndSelf()->all()
        );

        $expected = [
            $this->categories('A2'),
            $this->categories('A3'),
        ];

        $this->assertEquals(
            $expected,
            $this->categories('A2')->getSiblingsAndSelf()->all()
        );

        $this->assertEquals(
            $expected,
            $this->categories('A3')->getSiblingsAndSelf()->all()
        );

        $expected = [
            $this->categories('B2.1'),
            $this->categories('B2.2'),
            $this->categories('B2.3'),
        ];

        $this->assertEquals(
            $expected,
            $this->categories('B2.1')->getSiblingsAndSelf()->all()
        );

        $this->assertEquals(
            $expected,
            $this->categories('B2.2')->getSiblingsAndSelf()->all()
        );

        $this->assertEquals(
            $expected,
            $this->categories('B2.3')->getSiblingsAndSelf()->all()
        );
    }

    public function testGetSiblings()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $expected = [
            $this->categories('B2.2'),
            $this->categories('B2.3'),
        ];

        $this->assertEquals(
            $expected,
            $this->categories('B2.1')->getSiblings()->all()
        );

        $expected = [
            $this->categories('B2.1'),
            $this->categories('B2.3'),
        ];

        $this->assertEquals(
            $expected,
            $this->categories('B2.2')->getSiblings()->all()
        );

        $expected = [
            $this->categories('B2.1'),
            $this->categories('B2.2'),
        ];

        $this->assertEquals(
            $expected,
            $this->categories('B2.3')->getSiblings()->all()
        );
    }

    public function testGetLeftSibling()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->assertEquals(
            $this->categories('B2.2'),
            $this->categories('B2.3')->getLeftSibling()
        );

        $this->assertEquals(
            $this->categories('B2.1'),
            $this->categories('B2.2')->getLeftSibling()
        );
    }

    public function testGetLeftSiblingOfFirstRootIsNull()
    {
        $build = Category::buildTree(PopulateData::basicTree());
        $this->assertNull(Category::first()->getLeftSibling());
    }

    public function testGetLeftSiblingWithNoneIsNull()
    {
        $build = Category::buildTree(PopulateData::deepTree());
        $this->assertNull($this->categories('B2.1')->getLeftSibling());
    }

    public function testGetLeftSiblingOfLeftmostNodeIsNull()
    {
        $build = Category::buildTree(PopulateData::deepTree());
        $this->assertNull($this->categories('A2')->getLeftSibling());
    }

    public function testGetRightSibling()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->assertEquals(
            $this->categories('B2.2'),
            $this->categories('B2.1')->getRightSibling()
        );

        $this->assertEquals(
            $this->categories('B2.3'),
            $this->categories('B2.2')->getRightSibling()
        );
    }

    public function testGetRightSiblingOfRoots()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $this->assertEquals(
            $this->categories('B1'),
            $this->categories('A1')->getRightSibling()
        );
    }

    public function testGetRightSiblingWithNoneIsNull()
    {
        $build = Category::buildTree(PopulateData::basicTree());
        $this->assertNull($this->categories('B1')->getRightSibling());
    }

    public function testGetRightSiblingOfRightmostNodeIsNull()
    {
        $build = Category::buildTree(PopulateData::deepTree());
        $this->assertNull($this->categories('B3')->getRightSibling());
    }

    public function testInsideSubtree()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->assertFalse(
            $this->categories('B2')->insideSubtree(
                $this->categories('A1')
            )
        );

        $this->assertFalse(
            $this->categories('B2.1')->insideSubtree(
                $this->categories('A1')
            )
        );

        $this->assertTrue(
            $this->categories('B2')->insideSubtree(
                $this->categories('B1')
            )
        );

        $this->assertTrue(
            $this->categories('B2.1')->insideSubtree(
                $this->categories('B1')
            )
        );

        $this->assertTrue(
            $this->categories('B2.1')->insideSubtree(
                $this->categories('B2')
            )
        );
    }

    public function testGetLevel()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $this->assertEquals(0, $this->categories('B1')->getLevel());
        $this->assertEquals(1, $this->categories('B2')->getLevel());
        $this->assertEquals(2, $this->categories('B2.1')->getLevel());
    }

    public function testToHierarchyReturnsAnEloquentCollection()
    {
        $build = Category::buildTree(PopulateData::deepTree());
        $categories = Category::all()->toHierarchy();
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $categories);
    }

    public function testToHierarchyReturnsHierarchicalData()
    {
        $build = Category::buildTree(PopulateData::basicTree());
        $categories = Category::all()->toHierarchy();

        $this->assertEquals(2, $categories->count());

        $first = $categories->first();
        $this->assertEquals('A1', $first->name);
        $this->assertEquals(2, $first->children->count());

        $first_lvl2 = $first->children->first();
        $this->assertEquals('A2', $first_lvl2->name);
        $this->assertEquals(0, $first_lvl2->children->count());
    }

    public function testToHierarchyNestsCorrectly()
    {
        // Prune all categories
        Category::query()->delete();

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
        $a = Category::create(['name' => 'A']);
        $b = Category::create(['name' => 'B']);
        $c = Category::create(['name' => 'C']);
        $d = Category::create(['name' => 'D']);

        $ch = Category::create(['name' => 'A.1']);
        $ch->makeChildOf($a);

        $ch = Category::create(['name' => 'A.2']);
        $ch->makeChildOf($a);

        $ch = Category::create(['name' => 'B.1']);
        $ch->makeChildOf($b);

        $ch = Category::create(['name' => 'B.2']);
        $ch->makeChildOf($b);

        $ch2 = Category::create(['name' => 'B.2.1']);
        $ch2->makeChildOf($ch);

        $ch2 = Category::create(['name' => 'B.2.2']);
        $ch2->makeChildOf($ch);

        $ch3 = Category::create(['name' => 'B.2.2.1']);
        $ch3->makeChildOf($ch2);

        $ch2 = Category::create(['name' => 'B.2.3']);
        $ch2->makeChildOf($ch);

        $ch = Category::create(['name' => 'B.3']);
        $ch->makeChildOf($b);

        $ch = Category::create(['name' => 'C.1']);
        $ch->makeChildOf($c);

        $ch = Category::create(['name' => 'C.2']);
        $ch->makeChildOf($c);

        $this->assertTrue(Category::isValidNestedSet());

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
        $wholeTree = $this->hierarchy(Category::all()->toHierarchy()->toArray());
        $this->assertArraysAreEqual($expectedWholeTree, $wholeTree);

        $subtreeA = $this->hierarchy($this->categories('A')->getDescendantsAndSelf()->toHierarchy()->toArray());
        $this->assertArraysAreEqual($expectedSubtreeA, $subtreeA);

        $subtreeB = $this->hierarchy($this->categories('B')->getDescendantsAndSelf()->toHierarchy()->toArray());
        $this->assertArraysAreEqual($expectedSubtreeB, $subtreeB);

        $subtreeC = $this->hierarchy($this->categories('C')->getDescendants()->toHierarchy()->toArray());
        $this->assertArraysAreEqual($expectedSubtreeC, $subtreeC);

        $subtreeD = $this->hierarchy($this->categories('D')->getDescendantsAndSelf()->toHierarchy()->toArray());
        $this->assertArraysAreEqual($expectedSubtreeD, $subtreeD);

        $this->assertTrue($this->categories('D')->getDescendants()->toHierarchy()->isEmpty());
    }

    public function testToHierarchyNestsCorrectlyNotSequential()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $root = Category::create(['name' => 'C1']);
        $root->children()->create(['name' => 'C2.1']);
        $root->children()->create(['name' => 'C2.2']);

        $this->assertTrue(Category::isValidNestedSet());

        $expected = [
            'C1' => [
                'C2.1' => null,
                'C2.2' => null,
            ],
        ];

        $root->reload();
        $this->assertArraysAreEqual(
            $expected,
            $this->hierarchy(
                $this->categories('C1')->getDescendantsAndSelf()->toHierarchy()->toArray()
            )
        );
    }

    public function testGetNestedList()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $seperator = ' ';
        $nestedList = Category::getNestedList('name', 'id', $seperator);

        $expected = [
            1 => str_repeat($seperator, 0) . 'A1',
            2 => str_repeat($seperator, 1) . 'A2',
            3 => str_repeat($seperator, 1) . 'A3',
            4 => str_repeat($seperator, 0) . 'B1',
            5 => str_repeat($seperator, 1) . 'B2',
            7 => str_repeat($seperator, 2) . 'B2.1',
            8 => str_repeat($seperator, 2) . 'B2.2',
            9 => str_repeat($seperator, 2) . 'B2.3',
            6 => str_repeat($seperator, 1) . 'B3',
        ];

        $this->assertArraysAreEqual($expected, $nestedList);
    }

    public function testGetNestedListSymbol()
    {
        $build = Category::buildTree(PopulateData::deepTree());

        $symbol = '- ';
        $seperator = ' ';
        $nestedList = Category::getNestedList('name', 'id', $seperator, $symbol);

        $expected = [
            1 => str_repeat($seperator, 0) . $symbol . 'A1',
            2 => str_repeat($seperator, 1) . $symbol . 'A2',
            3 => str_repeat($seperator, 1) . $symbol . 'A3',
            4 => str_repeat($seperator, 0) . $symbol . 'B1',
            5 => str_repeat($seperator, 1) . $symbol . 'B2',
            7 => str_repeat($seperator, 2) . $symbol . 'B2.1',
            8 => str_repeat($seperator, 2) . $symbol . 'B2.2',
            9 => str_repeat($seperator, 2) . $symbol . 'B2.3',
            6 => str_repeat($seperator, 1) . $symbol . 'B3',
        ];

        $this->assertArraysAreEqual($expected, $nestedList);
    }
}

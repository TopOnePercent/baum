<?php

namespace Baum\Tests\Main\Standard;

use Baum\Exceptions\MoveNotPossibleException;
use Baum\Tests\Main\Concerns\NodeModelExtensionsTest;
use Baum\Tests\Main\Models\MultiScopedCategory;
use Baum\Tests\Main\Models\OrderedScopedCategory;
use Baum\Tests\Main\Models\ScopedCategory;
use Baum\Tests\Main\Support\MyTrait;
use Baum\Tests\Main\Support\PopulateData;
use Baum\Tests\Main\UnitAbstract;

class CategoryMultiScopedTest extends UnitAbstract
{
//     public function setUp()
//     {
//         parent::setUp();
//
//         with(new CategoryMigrator())->up();
//         with(new MultiScopedCategorySeeder())->run();
//     }

    use MyTrait, NodeModelExtensionsTest;

    public function testInSameScope()
    {
        $build = ScopedCategory::buildTree(PopulateData::multiScoped());

        $root1 = $this->categories('Root 1', ScopedCategory::class);
        $child1 = $this->categories('Child 1', ScopedCategory::class);
        $child2 = $this->categories('Child 2', ScopedCategory::class);

        $root2 = $this->categories('Root 2', ScopedCategory::class);
        $child4 = $this->categories('Child 4', ScopedCategory::class);
        $child5 = $this->categories('Child 5', ScopedCategory::class);

        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $this->assertTrue($root1->inSameScope($child1));
        $this->assertTrue($child1->inSameScope($child2));
        $this->assertTrue($child2->inSameScope($root1));

        $this->assertTrue($root2->inSameScope($child4));
        $this->assertTrue($child4->inSameScope($child5));
        $this->assertTrue($child5->inSameScope($root2));

        $this->assertFalse($root1->inSameScope($root2));
        $this->assertFalse($root2->inSameScope($root1));

        $this->assertFalse($child1->inSameScope($child4));
        $this->assertFalse($child4->inSameScope($child1));

        $this->assertFalse($child2->inSameScope($child5));
        $this->assertFalse($child5->inSameScope($child2));
    }

    public function testInSameScopeMultiple()
    {
        $build = ScopedCategory::buildTree(PopulateData::multiScoped());

        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $child1 = $this->categories('Child 1', MultiScopedCategory::class);
        $child2 = $this->categories('Child 2', MultiScopedCategory::class);

        $child4 = $this->categories('Child 4', MultiScopedCategory::class);
        $child5 = $this->categories('Child 5', MultiScopedCategory::class);

        $enfant1 = $this->categories('Enfant 1', MultiScopedCategory::class);
        $enfant2 = $this->categories('Enfant 2', MultiScopedCategory::class);

        $hijo1 = $this->categories('Hijo 1', MultiScopedCategory::class);
        $hijo2 = $this->categorieS('Hijo 2', MultiScopedCategory::class);

        $this->assertTrue($child1->inSameScope($child2));
        $this->assertTrue($child4->inSameScope($child5));
        $this->assertTrue($enfant1->inSameScope($enfant2));
        $this->assertTrue($hijo1->inSameScope($hijo2));

        $this->assertFalse($child2->inSameScope($child4));
        $this->assertFalse($child5->inSameScope($enfant1));
        $this->assertFalse($enfant2->inSameScope($hijo1));
        $this->assertFalse($hijo2->inSameScope($child1));
    }

    public function testIsSelfOrAncestorOf()
    {
        $build = ScopedCategory::buildTree(PopulateData::multiScoped());

        $root1 = $this->categories('Root 1', ScopedCategory::class);
        $child21 = $this->categories('Child 2.1', ScopedCategory::class);

        $root2 = $this->categories('Root 2', ScopedCategory::class);
        $child51 = $this->categories('Child 5.1', ScopedCategory::class);

        $this->assertTrue($root1->isSelfOrAncestorOf($child21));
        $this->assertTrue($root2->isSelfOrAncestorOf($child51));

        $this->assertFalse($root1->isSelfOrAncestorOf($child51));
        $this->assertFalse($root2->isSelfOrAncestorOf($child21));
    }

    public function testIsSelfOrDescendantOf()
    {
        $build = ScopedCategory::buildTree(PopulateData::multiScoped());

        $root1 = $this->categories('Root 1', ScopedCategory::class);
        $child21 = $this->categories('Child 2.1', ScopedCategory::class);

        $root2 = $this->categories('Root 2', ScopedCategory::class);
        $child51 = $this->categories('Child 5.1', ScopedCategory::class);

        $this->assertTrue($child21->isSelfOrDescendantOf($root1));
        $this->assertTrue($child51->isSelfOrDescendantOf($root2));

        $this->assertFalse($child21->isSelfOrDescendantOf($root2));
        $this->assertFalse($child51->isSelfOrDescendantOf($root1));
    }

    public function testGetSiblingsAndSelf()
    {
        $build = ScopedCategory::buildTree(PopulateData::multiScoped());

        $root2 = $this->categories('Root 2', ScopedCategory::class);

        $child1 = $this->categories('Child 1', ScopedCategory::class);
        $child2 = $this->categories('Child 2', ScopedCategory::class);
        $child3 = $this->categories('Child 3', ScopedCategory::class);

        $expected = [$root2];
        $this->assertEquals($expected, $root2->getSiblingsAndSelf()->all());

        $expected = [$child1, $child2, $child3];
        $this->assertEquals($expected, $child2->getSiblingsAndSelf()->all());
    }

    public function testGetSiblingsAndSelfMultiple()
    {
        $build = ScopedCategory::buildTree(PopulateData::multiScoped());

        $root1 = $this->categories('Racine 1', MultiScopedCategory::class);

        $child1 = $this->categories('Hijo 1', MultiScopedCategory::class);
        $child2 = $this->categories('Hijo 2', MultiScopedCategory::class);
        $child3 = $this->categories('Hijo 3', MultiScopedCategory::class);

        $expected = [$root1];
        $this->assertEquals($expected, $root1->getSiblingsAndSelf()->all());

        $expected = [$child1, $child2, $child3];
        $this->assertEquals($expected, $child3->getSiblingsAndSelf()->all());
    }

    public function testSimpleMovementsMultiple()
    {
        $build = ScopedCategory::buildTree(PopulateData::multiScoped());

        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $root2 = MultiScopedCategory::create(['name' => 'Raiz 2', 'company_id' => 3, 'language' => 'es']);
        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $this->categories('Hijo 1', MultiScopedCategory::class)->makeChildOf($root2);
        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $root2->reload();
        $expected = [$this->categories('Hijo 1', MultiScopedCategory::class)];
        $this->assertEquals($expected, $root2->children()->get()->all());
    }

    public function testSimpleSubtreeMovementsMultiple()
    {
        $build = ScopedCategory::buildTree(PopulateData::multiScoped());

        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $root2 = MultiScopedCategory::create(['name' => 'Raiz 2', 'company_id' => 3, 'language' => 'es']);
        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $this->categories('Hijo 2', MultiScopedCategory::class)->makeChildOf($root2);
        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $root2->reload();
        $expected = [
            $this->categories('Hijo 2', MultiScopedCategory::class),
            $this->categories('Hijo 2.1', MultiScopedCategory::class),
        ];
        $this->assertEquals($expected, $root2->getDescendants()->all());
    }

    public function testFullSubtreeMovementsMultiple()
    {
        $build = ScopedCategory::buildTree(PopulateData::multiScoped());

        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $root2 = MultiScopedCategory::create(['name' => 'Raiz 2', 'company_id' => 3, 'language' => 'es']);
        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $this->categories('Raiz 1', MultiScopedCategory::class)->makeChildOf($root2);
        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $root2->reload();
        $expected = [
            $this->categories('Raiz 1', MultiScopedCategory::class),
            $this->categories('Hijo 1', MultiScopedCategory::class),
            $this->categories('Hijo 2', MultiScopedCategory::class),
            $this->categories('Hijo 2.1', MultiScopedCategory::class),
            $this->categories('Hijo 3', MultiScopedCategory::class),
        ];
        $this->assertEquals($expected, $root2->getDescendants()->all());
    }

    public function testToHierarchyNestsCorrectlyWithScopedOrder()
    {
        $this->markTestSkipped();
        $build = OrderedScopedCategory::buildTree(PopulateData::multiScoped());
//
//         $expectedWhole1 = [
//             'Root 1' => [
//                 'Child 1' => null,
//                 'Child 2' => [
//                     'Child 2.1' => null,
//                 ],
//                 'Child 3' => null,
//             ],
//         ];
//
//         $expectedWhole2 = [
//             'Root 2' => [
//                 'Child 4' => null,
//                 'Child 5' => [
//                     'Child 5.1' => null,
//                 ],
//                 'Child 6' => null,
//             ],
//         ];
//
// //         $this->assertArraysAreEqual($expectedWhole1, $this->hmap(OrderedScopedCategory::where('company_id', 1)->get()->toHierarchy()->toArray()));
// //         $this->assertArraysAreEqual($expectedWhole2, $this->hmap(OrderedScopedCategory::where('company_id', 2)->get()->toHierarchy()->toArray()));
//
// 		$parent = $this->categories('Root 1', OrderedScopedCategory::class);
//
//         $expected = [
//             $parent,
//             $this->categories('Child 1', OrderedScopedCategory::class),
//             $this->categories('Child 2', OrderedScopedCategory::class),
//             $this->categories('Child 2.1', OrderedScopedCategory::class),
//             $this->categories('Child 3', OrderedScopedCategory::class),
//
//         ];
//
//         $this->assertEquals($expected, $parent->getDescendantsAndSelf()->all());
//
// 		//$parent->getDescendantsAndSelf()->all()
//
//
    }

    /**
     * expectedException Baum\Exceptions\MoveNotPossibleException.
     */
    public function testNodesCannotMoveBetweenScopes()
    {
        $build = ScopedCategory::buildTree(PopulateData::multiScoped());

        $child4 = $this->categories('Child 4', ScopedCategory::class);
        $root1 = $this->categories('Root 1', ScopedCategory::class);
        $this->expectException(MoveNotPossibleException::class);

        $child4->makeChildOf($root1);
    }

    /**
     * expectedException Baum\Exceptions\MoveNotPossibleException.
     */
    public function testNodesCannotMoveBetweenScopesMultiple()
    {
        $build = ScopedCategory::buildTree(PopulateData::multiScoped());

        $root1 = $this->categories('Root 1', MultiScopedCategory::class);
        $child4 = $this->categories('Child 4', MultiScopedCategory::class);

        $this->expectException(MoveNotPossibleException::class);

        $child4->makeChildOf($root1);
    }

    /**
     * expectedException Baum\Exceptions\MoveNotPossibleException.
     */
    public function testNodesCannotMoveBetweenScopesMultiple2()
    {
        $build = ScopedCategory::buildTree(PopulateData::multiScoped());
        $this->expectException(MoveNotPossibleException::class);

        $root1 = $this->categories('Racine 1', MultiScopedCategory::class);
        $child2 = $this->categories('Hijo 2', MultiScopedCategory::class);

        $child2->makeChildOf($root1);
    }

    // TODO: Moving nodes between scopes is problematic ATM. Fix it or find a work-around.
    public function testMoveNodeBetweenScopes()
    {
        $this->markTestSkipped();

        // $root1    = Menu::create(array('caption' => 'TL1', 'site_id' => 1, 'language' => 'en'));
    // $child11  = Menu::create(array('caption' => 'C11', 'site_id' => 1, 'language' => 'en'));
    // $child12  = Menu::create(array('caption' => 'C12', 'site_id' => 1, 'language' => 'en'));

    // $this->assertTrue(Menu::isValidNestedSet());

    // $child11->makeChildOf($root1);
    // $child12->makeChildOf($root1);

    // $this->assertTrue(Menu::isValidNestedSet());

    // $root2    = Menu::create(array('caption' => 'TL2', 'site_id' => 2, 'language' => 'en'));
    // $child21  = Menu::create(array('caption' => 'C21', 'site_id' => 2, 'language' => 'en'));
    // $child22  = Menu::create(array('caption' => 'C22', 'site_id' => 2, 'language' => 'en'));
    // $child21->makeChildOf($root2);
    // $child22->makeChildOf($root2);

    // $this->assertTrue(Menu::isValidNestedSet());

    // $child11->update(array('site_id' => 2));
    // $child11->makeChildOf($root2);

    // $this->assertTrue(Menu::isValidNestedSet());

    // $expected = array($this->menus('C12'));
    // $this->assertEquals($expected, $root1->children()->get()->all());

    // $expected = array($this->menus('C21'), $this->menus('C22'), $this->menus('C11'));
    // $this->assertEquals($expected, $root2->children()->get()->all());
    }
}

<?php

namespace Baum\Tests\Main\Standard;

use Baum\Tests\Main\Concerns\NodeModelExtensionsTestTrait;
use Baum\Tests\Main\Models\Category;
use Baum\Tests\Main\Models\MultiScopedCategory;
use Baum\Tests\Main\Models\OrderedCategory;
use Baum\Tests\Main\Models\ScopedCategory;
use Baum\Tests\Main\Support\PopulateData;
use Baum\Tests\Main\UnitAbstract;

//use Baum\Tests\Suite\Support\Testable;
//use Baum\Tests\Suite\Support\Cast;

class CategoryColumnsTest extends UnitAbstract
{
    //use Cast, Testable;
    use NodeModelExtensionsTestTrait;

    public function testGetParentColumnName()
    {
        $category = new Category();

        $this->assertEquals(with(new Category())->getParentColumnName(), 'parent_id');
    }

    public function testGetQualifiedParentColumnName()
    {
        $category = new Category();

        $this->assertEquals($category->getQualifiedParentColumnName(), 'categories.parent_id');
    }

    public function testGetParentId()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $child = Category::find(6);
        $this->assertEquals(4, $child->getRoot()->id);

        //$this->assertNull($this->categories('Root 1')->getParentId());

        //$this->assertEquals($this->categories('Child 1')->getParentId(), 1);
        //print_r($stub);
    }

    public function testGetLeftColumnName()
    {
        $category = new Category();

        $this->assertEquals($category->getLeftColumnName(), 'lft');
    }

    public function testGetQualifiedLeftColumnName()
    {
        $category = new Category();
        $this->assertEquals($category->getQualifiedLeftColumnName(), 'categories.lft');
    }

    public function testGetLeft()
    {
        $build = Category::buildTree(PopulateData::basicTree());
//      $child = Category::find(3);
        $this->assertEquals(4, Category::find(3)->getLeft());
        $this->assertEquals(7, Category::find(4)->getLeft());
    }

    public function testGetRightColumnName()
    {
        $category = new Category();

        $this->assertEquals($category->getRightColumnName(), 'rgt');
    }

    public function testGetQualifiedRightColumnName()
    {
        $category = new Category();
        $this->assertEquals($category->getQualifiedRightColumnName(), 'categories.rgt');
    }

    public function testGetRight()
    {
        $build = Category::buildTree(PopulateData::basicTree());
//      $child = Category::find(5);
        $this->assertEquals(5, Category::find(3)->getRight());
        $this->assertEquals(11, Category::find(6)->getRight());
    }

    public function testGetOrderColumName()
    {
        $init = new Category();
        $this->assertEquals($init->getOrderColumnName(), $init->getLeftColumnName());
    }

    public function testGetQualifiedOrderColumnName()
    {
        $init = new Category();
        $this->assertEquals($init->getQualifiedOrderColumnName(), $init->getQualifiedLeftColumnName());
    }

    public function testGetOrder()
    {
        $build = Category::buildTree(PopulateData::basicTree());
        $category = Category::categories('A1');
        $this->assertEquals($category->getOrder(), $category->getLeft());
    }

    public function testGetOrderColumnNameNonDefault()
    {
        $init = new OrderedCategory();
        $this->assertEquals($init->getOrderColumnName(), 'name');
    }

    public function testGetQualifiedOrderColumnNameNonDefault()
    {
        $init = new OrderedCategory();
        $this->assertEquals($init->getQualifiedOrderColumnName(), 'categories.name');
    }

    public function testGetOrderNonDefault()
    {
        $build = OrderedCategory::buildTree(PopulateData::basicTree());
        $category = $this->categories('A1', OrderedCategory::class);
        $this->assertEquals($category->getOrder(), 'A1');
    }

    public function testGetScopedColumns()
    {
        $category = new Category();
        $this->assertEquals($category->getScopedColumns(), []);

        $category = new ScopedCategory();
        $this->assertEquals($category->getScopedColumns(), ['company_id']);

        $category = new MultiScopedCategory();
        $this->assertEquals($category->getScopedColumns(), ['company_id', 'language']);
    }

    public function testGetQualifiedScopedColumns()
    {
        $category = new Category();
        $this->assertEquals($category->getQualifiedScopedColumns(), []);

        $category = new ScopedCategory();
        $this->assertEquals($category->getQualifiedScopedColumns(), ['categories.company_id']);

        $category = new MultiScopedCategory();
        $this->assertEquals($category->getQualifiedScopedColumns(), ['categories.company_id', 'categories.language']);
    }

    public function testIsScoped()
    {
        $category = new Category();
        $this->assertFalse($category->isScoped());

        $category = new ScopedCategory();
        $this->assertTrue($category->isScoped());

        $category = new MultiScopedCategory();
        $this->assertTrue($category->isScoped());

        $category = new OrderedCategory();
        $this->assertFalse($category->isScoped());
    }

    public function testGetOthersAtSameDepth()
    {
        $build = Category::buildTree(PopulateData::basicTree());

        $this->assertEquals(1, $this->categories('A1')->getOthersAtSameDepth()->count());
//         $this->assertEquals('Root 2', $this->categories('Root 1')->getOthersAtSameDepth()->first()->name);
//
//         $this->assertEquals(2, $this->categories('Child 1')->getOthersAtSameDepth()->count());
//         $this->assertEquals(0, $this->categories('Child 2.1')->getOthersAtSameDepth()->count());
//
//         $this->assertEquals('Child 2', $this->categories('Child 1')->getOthersAtSameDepth()->get()[0]->name);
//         $this->assertEquals('Child 3', $this->categories('Child 1')->getOthersAtSameDepth()->get()[1]->name);
    }
}

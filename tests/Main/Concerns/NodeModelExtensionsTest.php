<?php

namespace Baum\Tests\Main\Concerns;

//use Baum\Tests\Main\UnitAbstract;
//use Orchestra\Testbench\TestCase;

use Baum\Tests\Main\Models\Category;
use Baum\Tests\Main\Models\MultiScopedCategory;
use Baum\Tests\Main\Models\OrderedCategory;
use Baum\Tests\Main\Models\ScopedCategory;
use Baum\Tests\Main\Models\SoftCategory;
use Baum\Tests\Main\Support\PopulateData;

trait NodeModelExtensionsTest
{
    protected function categories($name, $className = Category::class)
    {
        return forward_static_call_array([$className, 'where'], ['name', '=', $name])->first();
    }

    public function testNewQueryReturnsEloquentBuilderWithExtendedQueryBuilder()
    {
        $query = with(new Category())->newQuery()->getQuery();

        $this->assertInstanceOf('Baum\Extensions\Query\Builder', $query);
    }

    public function testNewCollectionReturnsCustomOne()
    {
        $this->assertInstanceOf('\Baum\Extensions\Eloquent\Collection', with(new Category())->newCollection());
    }

    public function testGetObservableEventsIncludesMovingEvents()
    {
        $events = with(new Category())->getObservableEvents();

        $this->assertContains('moving', $events);
        $this->assertContains('moved', $events);
    }

    public function testAreSoftDeletesEnabled()
    {
        $this->assertFalse(with(new Category())->areSoftDeletesEnabled());
        $this->assertTrue(with(new SoftCategory())->areSoftDeletesEnabled());
    }

    public function testSoftDeletesEnabledStatic()
    {
        $this->assertFalse(Category::softDeletesEnabled());
        $this->assertTrue(SoftCategory::softDeletesEnabled());
    }

    public function nodeModelMoving()
    {
        // $dispatcher = Category::getEventDispatcher();

        // $events->shouldReceive('listen')->once()->with('eloquent.moving: '.get_class(new Category()), $closure, 0);
        // Category::moving($closure);

        // Category::unsetEventDispatcher();

        // Category::setEventDispatcher($dispatcher);
    }

    public function nodeModelMoved()
    {
        // $dispatcher = Category::getEventDispatcher();

        // $closure = function () {
        // };
        // $events->shouldReceive('listen')->once()->with('eloquent.moved: '.get_class(new Category()), $closure, 0);
        // Category::moved($closure);

        // Category::unsetEventDispatcher();

        // Category::setEventDispatcher($dispatcher);
    }

    public function testReloadResetsChangesOnFreshNodes()
    {
        $new = new Category();

        $new->name = 'Some new category';
        $new->reload();

        $this->assertNull($new->name);
    }

    public function testReloadResetsChangesOnPersistedNodes()
    {
        $node = Category::create(['name' => 'Some node']);

        $node->name = 'A better node';
        $node->lft = 10;
        $node->reload();

        $this->assertEquals($this->categories('Some node')->getAttributes(), $node->getAttributes());
    }

    public function testReloadResetsChangesOnDeletedNodes()
    {
        $node = Category::create(['name' => 'Some node']);
        $this->assertNotNull($node->getKey());

        $node->delete();
        $this->assertNull($this->categories('Some node'));

        $node->name = 'A better node';
        $node->reload();

        $this->assertEquals('Some node', $node->name);
    }

//     public function testReloadThrowsExceptionIfNodeCannotBeLocated()
//     {
    //      $build = Category::buildTree(PopulateData::basicTree());
//
//         $node = Category::create(['name' => 'Some node']);
//         $this->expectException($node->getKey());
//
//         $node->delete();
//         $this->expectException($this->categories('Some node'));
//         $this->assertFalse($node->exists);
//
//         // Fake persisted state, reload & expect failure
//         $node->exists = true;
//         $node->reload();
//     }

    public function testNewNestedSetQueryUsesInternalBuilder()
    {
        $category = new Category();
        $builder = $category->newNestedSetQuery();
        $query = $builder->getQuery();

        $this->assertInstanceOf('Baum\Extensions\Query\Builder', $query);
    }

    public function testNewNestedSetQueryIsOrderedByDefault()
    {
        $category = new Category();
        $builder = $category->newNestedSetQuery();
        $query = $builder->getQuery();

        $this->assertEmpty($query->wheres);
        $this->assertNotEmpty($query->orders);
        $this->assertEquals($category->getLeftColumnName(), $category->getOrderColumnName());
        $this->assertEquals($category->getQualifiedLeftColumnName(), $category->getQualifiedOrderColumnName());
        $this->assertEquals($category->getQualifiedOrderColumnName(), $query->orders[0]['column']);
    }

    public function testNewNestedSetQueryIsOrderedByCustom()
    {
        $category = new OrderedCategory();
        $builder = $category->newNestedSetQuery();
        $query = $builder->getQuery();

        $this->assertEmpty($query->wheres);
        $this->assertNotEmpty($query->orders);
        $this->assertEquals('name', $category->getOrderColumnName());
        $this->assertEquals('categories.name', $category->getQualifiedOrderColumnName());
        $this->assertEquals($category->getQualifiedOrderColumnName(), $query->orders[0]['column']);
    }

    public function testNewNestedSetQueryIncludesScopedColumns()
    {
        $category = new Category();
        $simpleQuery = $category->newNestedSetQuery()->getQuery();
        $this->assertEmpty($simpleQuery->wheres);

        $scopedCategory = new ScopedCategory();
        $scopedQuery = $scopedCategory->newNestedSetQuery()->getQuery();
        $this->assertCount(1, $scopedQuery->wheres);
        $this->assertEquals($scopedCategory->getScopedColumns(), array_map(function ($elem) {
            return $elem['column'];
        }, $scopedQuery->wheres));

        $multiScopedCategory = new MultiScopedCategory();
        $multiScopedQuery = $multiScopedCategory->newNestedSetQuery()->getQuery();
        $this->assertCount(2, $multiScopedQuery->wheres);
        $this->assertEquals($multiScopedCategory->getScopedColumns(), array_map(function ($elem) {
            return $elem['column'];
        }, $multiScopedQuery->wheres));
    }
}

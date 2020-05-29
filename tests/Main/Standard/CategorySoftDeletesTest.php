<?php

namespace Baum\Tests\Main\Standard;

// class CategorySoftDeletesTest extends CategoryAbstract
// {
//     public function testReload()
//     {
//         $node = $this->categories('Child 3', 'SoftCategory');
//         $node->delete();

//         $this->assertTrue($node->trashed());

//         $node->reload();

//         $this->assertTrue($node->trashed());
//     }

//     public function testDeleteMaintainsTreeValid()
//     {
//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $child3 = $this->categories('Child 3', 'SoftCategory');
//         $child3->delete();

//         $this->assertTrue($child3->trashed());
//         $this->assertTrue(SoftCategory::isValidNestedSet());
//     }

//     public function testDeleteMaintainsTreeValidWithSubtrees()
//     {
//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $child2 = $this->categories('Child 2', 'SoftCategory');
//         $child2->delete();
//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $expected = [
//       $this->categories('Child 1', 'SoftCategory'),
//       $this->categories('Child 3', 'SoftCategory'),
//     ];
//         $this->assertEquals($expected, $this->categories('Root 1', 'SoftCategory')->getDescendants()->all());
//     }

//     public function testDeleteShiftsIndexes()
//     {
//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $this->categories('Child 1', 'SoftCategory')->delete();

//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $expected = [
//       $this->categories('Child 2', 'SoftCategory'),
//       $this->categories('Child 2.1', 'SoftCategory'),
//       $this->categories('Child 3', 'SoftCategory'),
//     ];
//         $this->assertEquals($expected, $this->categories('Root 1', 'SoftCategory')->getDescendants()->all());

//         $this->assertEquals(1, $this->categories('Root 1', 'SoftCategory')->getLeft());
//         $this->assertEquals(8, $this->categories('Root 1', 'SoftCategory')->getRight());

//         $this->assertEquals(2, $this->categories('Child 2', 'SoftCategory')->getLeft());
//         $this->assertEquals(5, $this->categories('Child 2', 'SoftCategory')->getRight());

//         $this->assertEquals(3, $this->categories('Child 2.1', 'SoftCategory')->getLeft());
//         $this->assertEquals(4, $this->categories('Child 2.1', 'SoftCategory')->getRight());

//         $this->assertEquals(6, $this->categories('Child 3', 'SoftCategory')->getLeft());
//         $this->assertEquals(7, $this->categories('Child 3', 'SoftCategory')->getRight());
//     }

//     public function testDeleteShiftsIndexesSubtree()
//     {
//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $this->categories('Child 2', 'SoftCategory')->delete();

//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $expected = [
//       $this->categories('Child 1', 'SoftCategory'),
//       $this->categories('Child 3', 'SoftCategory'),
//     ];
//         $this->assertEquals($expected, $this->categories('Root 1', 'SoftCategory')->getDescendants()->all());

//         $this->assertEquals(1, $this->categories('Root 1', 'SoftCategory')->getLeft());
//         $this->assertEquals(6, $this->categories('Root 1', 'SoftCategory')->getRight());

//         $this->assertEquals(2, $this->categories('Child 1', 'SoftCategory')->getLeft());
//         $this->assertEquals(3, $this->categories('Child 1', 'SoftCategory')->getRight());

//         $this->assertEquals(4, $this->categories('Child 3', 'SoftCategory')->getLeft());
//         $this->assertEquals(5, $this->categories('Child 3', 'SoftCategory')->getRight());
//     }

//     public function testDeleteShiftsIndexesFullSubtree()
//     {
//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $this->categories('Root 1', 'SoftCategory')->delete();
//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $this->assertEmpty($this->categories('Root 2', 'SoftCategory')->getSiblings()->all());
//         $this->assertEquals(1, $this->categories('Root 2', 'SoftCategory')->getLeft());
//         $this->assertEquals(2, $this->categories('Root 2', 'SoftCategory')->getRight());
//     }

//     public function testRestoreMaintainsTreeValid()
//     {
//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $child3 = $this->categories('Child 3', 'SoftCategory');
//         $child3->delete();

//         $this->assertTrue($child3->trashed());
//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $child3->restore();

//         dd($child3->deleted_at, $child3->trashed());
//         // exit;

//         $this->assertFalse($child3->trashed());
//         $this->assertTrue(SoftCategory::isValidNestedSet());
//     }

//     public function testRestoreMaintainsTreeValidWithSubtrees()
//     {
//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $expected = collect([
//             $this->categories('Child 1', 'SoftCategory'),
//             $this->categories('Child 2', 'SoftCategory'),
//             $this->categories('Child 2.1', 'SoftCategory'),
//             $this->categories('Child 3', 'SoftCategory'),
//         ]);

//         // exit;

//         $child2 = $this->categories('Child 2', 'SoftCategory');

//         $child2->delete();

//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         // $child2->reload();
//         $child2->restore();

//         // dd($child2->getAttributes(), $child2->getOriginal());

//         // var_dump($child2->getAttributes());

//         // exit;

//         // $this->assertTrue(SoftCategory::isValidNestedSet());

//         exit;

//         $all = $this->categories('Root 1', 'SoftCategory')->getDescendants()->toArray();

//         // dd($this->categories('Root 1', 'SoftCategory')->getDescendants());

//         // dd($expected->toArray(), $all);

//         $this->assertEquals(
//             $expected->toArray(),
//             $all
//         );

//         exit;
//     }

//     public function testRestoreUnshiftsIndexes()
//     {
//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $this->categories('Child 1', 'SoftCategory')->delete();

//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         SoftCategory::withTrashed()->where('name', 'Child 1')->first()->restore();

//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $expected = [
//       $this->categories('Child 1', 'SoftCategory'),
//       $this->categories('Child 2', 'SoftCategory'),
//       $this->categories('Child 2.1', 'SoftCategory'),
//       $this->categories('Child 3', 'SoftCategory'),
//     ];
//         $this->assertEquals($expected, $this->categories('Root 1', 'SoftCategory')->getDescendants()->all());

//         $this->assertEquals(1, $this->categories('Root 1', 'SoftCategory')->getLeft());
//         $this->assertEquals(10, $this->categories('Root 1', 'SoftCategory')->getRight());

//         $this->assertEquals(2, $this->categories('Child 1', 'SoftCategory')->getLeft());
//         $this->assertEquals(3, $this->categories('Child 1', 'SoftCategory')->getRight());

//         $this->assertEquals(4, $this->categories('Child 2', 'SoftCategory')->getLeft());
//         $this->assertEquals(7, $this->categories('Child 2', 'SoftCategory')->getRight());
//         $this->assertEquals(5, $this->categories('Child 2.1', 'SoftCategory')->getLeft());
//         $this->assertEquals(6, $this->categories('Child 2.1', 'SoftCategory')->getRight());

//         $this->assertEquals(8, $this->categories('Child 3', 'SoftCategory')->getLeft());
//         $this->assertEquals(9, $this->categories('Child 3', 'SoftCategory')->getRight());
//     }

//     public function testRestoreUnshiftsIndexesSubtree()
//     {
//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $this->categories('Child 2', 'SoftCategory')->delete();

//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         SoftCategory::withTrashed()->where('name', 'Child 2')->first()->restore();

//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $expected = [
//       $this->categories('Child 1', 'SoftCategory'),
//       $this->categories('Child 2', 'SoftCategory'),
//       $this->categories('Child 2.1', 'SoftCategory'),
//       $this->categories('Child 3', 'SoftCategory'),
//     ];
//         $this->assertEquals($expected, $this->categories('Root 1', 'SoftCategory')->getDescendants()->all());

//         $this->assertEquals(1, $this->categories('Root 1', 'SoftCategory')->getLeft());
//         $this->assertEquals(10, $this->categories('Root 1', 'SoftCategory')->getRight());

//         $this->assertEquals(2, $this->categories('Child 1', 'SoftCategory')->getLeft());
//         $this->assertEquals(3, $this->categories('Child 1', 'SoftCategory')->getRight());

//         $this->assertEquals(4, $this->categories('Child 2', 'SoftCategory')->getLeft());
//         $this->assertEquals(7, $this->categories('Child 2', 'SoftCategory')->getRight());
//         $this->assertEquals(5, $this->categories('Child 2.1', 'SoftCategory')->getLeft());
//         $this->assertEquals(6, $this->categories('Child 2.1', 'SoftCategory')->getRight());

//         $this->assertEquals(8, $this->categories('Child 3', 'SoftCategory')->getLeft());
//         $this->assertEquals(9, $this->categories('Child 3', 'SoftCategory')->getRight());
//     }

//     public function testRestoreUnshiftsIndexesFullSubtree()
//     {
//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $this->categories('Root 1', 'SoftCategory')->delete();

//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         SoftCategory::withTrashed()->where('name', 'Root 1')->first()->restore();

//         $this->assertTrue(SoftCategory::isValidNestedSet());

//         $expected = [
//       $this->categories('Child 1', 'SoftCategory'),
//       $this->categories('Child 2', 'SoftCategory'),
//       $this->categories('Child 2.1', 'SoftCategory'),
//       $this->categories('Child 3', 'SoftCategory'),
//     ];
//         $this->assertEquals($expected, $this->categories('Root 1', 'SoftCategory')->getDescendants()->all());

//         $this->assertEquals(1, $this->categories('Root 1', 'SoftCategory')->getLeft());
//         $this->assertEquals(10, $this->categories('Root 1', 'SoftCategory')->getRight());

//         $this->assertEquals(2, $this->categories('Child 1', 'SoftCategory')->getLeft());
//         $this->assertEquals(3, $this->categories('Child 1', 'SoftCategory')->getRight());

//         $this->assertEquals(4, $this->categories('Child 2', 'SoftCategory')->getLeft());
//         $this->assertEquals(7, $this->categories('Child 2', 'SoftCategory')->getRight());
//         $this->assertEquals(5, $this->categories('Child 2.1', 'SoftCategory')->getLeft());
//         $this->assertEquals(6, $this->categories('Child 2.1', 'SoftCategory')->getRight());

//         $this->assertEquals(8, $this->categories('Child 3', 'SoftCategory')->getLeft());
//         $this->assertEquals(9, $this->categories('Child 3', 'SoftCategory')->getRight());
//     }

//     public function testAllStatic()
//     {
//         $expected = ['Root 1', 'Child 1', 'Child 2', 'Child 2.1', 'Child 3', 'Root 2'];

//         $this->assertArraysAreEqual($expected, SoftCategory::all()->pluck('name')->toArray());
//     }

//     public function testAllStaticWithSoftDeletes()
//     {
//         $this->categories('Child 1', 'SoftCategory')->delete();
//         $this->categories('Child 3', 'SoftCategory')->delete();

//         $expected = ['Root 1', 'Child 2', 'Child 2.1', 'Root 2'];
//         $this->assertArraysAreEqual($expected, SoftCategory::all()->pluck('name')->toArray());
//     }
// }

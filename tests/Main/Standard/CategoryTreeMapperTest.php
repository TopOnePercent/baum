<?php

namespace Baum\Tests\Main\Standard;

use Baum\Tests\Main\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryTreeMapperTest extends CategoryAbstract
{
    public function testBuildTree()
    {
        $tree = $this->getDefaultTree();
        $this->assertTrue(Category::buildTree($tree));
        $this->assertTrue(Category::isValidNestedSet());

        $result = Category::all()->toHierarchy()->toArray();

        $a = flatten_tree($tree, ['name']);
        $b = flatten_tree($result, ['name']);

        $this->assertArraysAreEqual($a, $b);
    }

    public function testBuildTreePrunesAndInserts()
    {
        $tree = $this->getDefaultTree();
        $this->assertTrue(Category::buildTree($tree));
        $this->assertTrue(Category::isValidNestedSet());

        // Postgres fix
        if (DB::connection()->getDriverName() === 'pgsql') {
            $tablePrefix = DB::connection()->getTablePrefix();
            $sequenceName = $tablePrefix . 'categories_id_seq';
            DB::connection()->statement('ALTER SEQUENCE ' . $sequenceName . ' RESTART WITH 10');
        }

        $updated = [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
            ['id' => 3, 'name' => 'C', 'children' => [
                ['id' => 4, 'name' => 'C.1', 'children' => [
                    ['id' => 5, 'name' => 'C.1.1'],
                    ['id' => 6, 'name' => 'C.1.2'],
                ]],
                ['id' => 7, 'name' => 'C.2', 'children' => [
                    ['name' => 'C.2.1'],
                    ['name' => 'C.2.2'],
                ]],
            ]],
            ['id' => 9, 'name' => 'D'],
        ];
        $this->assertTrue(Category::buildTree($updated));
        $this->assertTrue(Category::isValidNestedSet());

        $expected = [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
            ['id' => 3, 'name' => 'C', 'children' => [
                ['id' => 4, 'name' => 'C.1', 'children' => [
                    ['id' => 5, 'name' => 'C.1.1'],
                    ['id' => 6, 'name' => 'C.1.2'],
                ]],
                ['id' => 7, 'name' => 'C.2', 'children' => [
                    ['id' => 10, 'name' => 'C.2.1'],
                    ['id' => 11, 'name' => 'C.2.2'],
                ]],
            ]],
            ['id' => 9, 'name' => 'D'],
        ];

        $hierarchy = Category::all()->toHierarchy()->toArray();
        $this->assertArraysAreEqual($expected, Category::arrayIntsKeys(Category::hmap($hierarchy, ['id', 'name'])));
    }

    public function testBuildTreeMoveNodes()
    {
        $this->getDefaultTree();
        $this->assertTrue(Category::isValidNestedSet());

        // Add some nodes
        $updated = [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
            ['id' => 3, 'name' => 'C', 'children' => [
                ['id' => 4, 'name' => 'C.1', 'children' => [
                    ['id' => 5, 'name' => 'C.1.1'],
                    ['id' => 6, 'name' => 'C.1.2'],
                ],
                ],
                ['id' => 7, 'name' => 'C.2', 'children' => [
                    ['name' => 'C.2.1'],
                    ['name' => 'C.2.2'],
                ],
                ],
            ],
            ],
            ['id' => 9, 'name' => 'D'],
        ];
        $this->assertTrue(Category::buildTree($updated));
        $this->assertTrue(Category::isValidNestedSet());

        // Move node 7 to be child of node 2
        $updated = [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B', 'children' => [
                ['id' => 7, 'name' => 'C.2', 'children' => [
                    ['id' => 10, 'name' => 'C.2.1'],
                    ['id' => 11, 'name' => 'C.2.2'],
                ],
                ],
            ],
            ],
            ['id' => 3, 'name' => 'C', 'children' => [
                ['id' => 4, 'name' => 'C.1', 'children' => [
                    ['id' => 5, 'name' => 'C.1.1'],
                    ['id' => 6, 'name' => 'C.1.2'],
                ],
                ],
            ],
            ],
            ['id' => 9, 'name' => 'D'],
        ];

        $this->assertTrue(Category::buildTree($updated));
        $this->assertTrue(Category::isValidNestedSet());

        $hierarchy = Category::all()->toHierarchy()->toArray();
        $this->assertArraysAreEqual($updated, Category::arrayIntsKeys(Category::hmap($hierarchy, ['id', 'name'])));
    }

    public function testMakeSubTree()
    {
        $root_1 = Category::create(['name' => 'Root 1']);

        $child_1 = Category::create(['name' => 'Child 1']);
        $child_1->makeChildOf($root_1);

        $parent = Category::create(['name' => 'Child 2']);
        $parent->makeChildOf($root_1);

        $subtree = [
            ['id' => 4, 'name' => 'Child 2.1'],
            ['name' => 'Child 2.2'],
            ['name' => 'Child 2.3', 'children' => [
                ['name' => 'Child 2.3.1', 'children' => [
                    ['name' => 'Child 2.3.1.1'],
                    ['name' => 'Child 2.3.1.1'],
                ],
                ],
                ['name' => 'Child 2.3.2'],
                ['name' => 'Child 2.3.3'],
            ],
            ],
            ['name' => 'Child 2.4'],
        ];

        $this->assertTrue($parent->makeTree($subtree));
        $this->assertTrue(Category::isValidNestedSet());

        $expected = [
            ['id' => 4, 'name' => 'Child 2.1'],
            ['id' => 5, 'name' => 'Child 2.2'],
            ['id' => 6, 'name' => 'Child 2.3', 'children' => [
                ['id' => 7, 'name' => 'Child 2.3.1', 'children' => [
                    ['id' => 8, 'name' => 'Child 2.3.1.1'],
                    ['id' => 9, 'name' => 'Child 2.3.1.1'],
                ],
                ],
                ['id' => 10, 'name' => 'Child 2.3.2'],
                ['id' => 11, 'name' => 'Child 2.3.3'],
            ],
            ],
            ['id' => 12, 'name' => 'Child 2.4'],
        ];

        $hierarchy = $parent->reload()->getDescendants()->toHierarchy()->toArray();
        $this->assertArraysAreEqual($expected, Category::arrayIntsKeys(Category::hmap($hierarchy, ['id', 'name'])));
    }

    public function testMakeTreePrunesAndInserts()
    {
        $root_1 = Category::create(['name' => 'Root 1']);

        $child_1 = Category::create(['name' => 'Child 1']);
        $child_1->makeChildOf($root_1);

        $parent = Category::create(['name' => 'Child 2']);
        $parent->makeChildOf($root_1);

        $subtree = [
            ['id' => 4, 'name' => 'Child 2.1'],
            ['name' => 'Child 2.2'],
            ['name' => 'Child 2.3', 'children' => [
                ['name' => 'Child 2.3.1', 'children' => [
                    ['name' => 'Child 2.3.1.1'],
                    ['name' => 'Child 2.3.1.1'],
                ]],
                ['name' => 'Child 2.3.2'],
                ['name' => 'Child 2.3.3'],
            ]],
            ['name' => 'Child 2.4'],
        ];

        $this->assertTrue($parent->makeTree($subtree));
        $this->assertTrue(Category::isValidNestedSet());

        $expected = [
            ['id' => 4, 'name' => 'Child 2.1'],
            ['id' => 5, 'name' => 'Child 2.2'],
            ['id' => 6, 'name' => 'Child 2.3', 'children' => [
                ['id' => 7, 'name' => 'Child 2.3.1', 'children' => [
                    ['id' => 8, 'name' => 'Child 2.3.1.1'],
                    ['id' => 9, 'name' => 'Child 2.3.1.1'],
                ]],
                ['id' => 10, 'name' => 'Child 2.3.2'],
                ['id' => 11, 'name' => 'Child 2.3.3'],
            ]],
            ['id' => 12, 'name' => 'Child 2.4'],
        ];

        $hierarchy = $parent->reload()->getDescendants()->toHierarchy()->toArray();
        $this->assertArraysAreEqual($expected, Category::arrayIntsKeys(Category::hmap($hierarchy, ['id', 'name'])));

        $modified = [
            ['id' => 7, 'name' => 'Child 2.2'],
            ['id' => 8, 'name' => 'Child 2.3'],
            ['id' => 14, 'name' => 'Child 2.4'],
            ['name' => 'Child 2.5', 'children' => [
                ['name' => 'Child 2.5.1', 'children' => [
                    ['name' => 'Child 2.5.1.1'],
                    ['name' => 'Child 2.5.1.1'],
                ]],
                ['name' => 'Child 2.5.2'],
                ['name' => 'Child 2.5.3'],
            ]],
        ];

        $this->assertTrue($parent->makeTree($modified));
        $this->assertTrue(Category::isValidNestedSet());

        $expected = [
            ['id' => 7, 'name' => 'Child 2.2'],
            ['id' => 8, 'name' => 'Child 2.3'],
            ['id' => 14, 'name' => 'Child 2.4'],
            ['id' => 15, 'name' => 'Child 2.5', 'children' => [
                ['id' => 16, 'name' => 'Child 2.5.1', 'children' => [
                    ['id' => 17, 'name' => 'Child 2.5.1.1'],
                    ['id' => 18, 'name' => 'Child 2.5.1.1'],
                ]],
                ['id' => 19, 'name' => 'Child 2.5.2'],
                ['id' => 20, 'name' => 'Child 2.5.3'],
            ]],
        ];

        $hierarchy = $parent->reload()->getDescendants()->toHierarchy()->toArray();
        $this->assertArraysAreEqual($expected, Category::arrayIntsKeys(Category::hmap($hierarchy, ['id', 'name'])));
    }

    public function testMakeTreeReordesNodes()
    {
        $root_1 = Category::create(['name' => 'Root 1']);

        $child_1 = Category::create(['name' => 'Child 1']);
        $child_1->makeChildOf($root_1);

        $parent = Category::create(['name' => 'Child 2']);
        $parent->makeChildOf($root_1);

        $subtree = [
            ['id' => 4, 'name' => 'Child 2.1'],
            ['name' => 'Child 2.2'],
            ['name' => 'Child 2.3', 'children' => [
                ['name' => 'Child 2.3.1', 'children' => [
                    ['name' => 'Child 2.3.1.1'],
                    ['name' => 'Child 2.3.1.1'],
                ]],
                ['name' => 'Child 2.3.2'],
                ['name' => 'Child 2.3.3'],
            ]],
            ['name' => 'Child 2.4'],
        ];

        $this->assertTrue($parent->makeTree($subtree));
        $this->assertTrue(Category::isValidNestedSet());

        $expected = [
            ['id' => 4, 'name' => 'Child 2.1'],
            ['id' => 5, 'name' => 'Child 2.2'],
            ['id' => 6, 'name' => 'Child 2.3', 'children' => [
                ['id' => 7, 'name' => 'Child 2.3.1', 'children' => [
                    ['id' => 8, 'name' => 'Child 2.3.1.1'],
                    ['id' => 9, 'name' => 'Child 2.3.1.1'],
                ]],
                ['id' => 10, 'name' => 'Child 2.3.2'],
                ['id' => 11, 'name' => 'Child 2.3.3'],
            ]],
            ['id' => 12, 'name' => 'Child 2.4'],
        ];

        $hierarchy = $parent->reload()->getDescendants()->toHierarchy()->toArray();
        $this->assertArraysAreEqual($expected, Category::arrayIntsKeys(Category::hmap($hierarchy, ['id', 'name'])));

        $modified = [
            ['id' => 7, 'name' => 'Child 2.2'],
            ['id' => 4, 'name' => 'Child 2.1'],
            ['id' => 8, 'name' => 'Child 2.3', 'children' => [
                ['id' => 9, 'name' => 'Child 2.3.1', 'children' => [
                    ['id' => 11, 'name' => 'Child 2.3.1.1'],
                    ['id' => 10, 'name' => 'Child 2.3.1.1'],
                ]],
                ['id' => 12, 'name' => 'Child 2.3.2'],
                ['id' => 13, 'name' => 'Child 2.3.3'],
            ]],
            ['id' => 14, 'name' => 'Child 2.4'],
        ];

        $this->assertTrue($parent->makeTree($modified));
        $this->assertTrue(Category::isValidNestedSet());

        $expected = [
            ['id' => 7, 'name' => 'Child 2.2'],
            ['id' => 4, 'name' => 'Child 2.1'],
            ['id' => 8, 'name' => 'Child 2.3', 'children' => [
                ['id' => 9, 'name' => 'Child 2.3.1', 'children' => [
                    ['id' => 11, 'name' => 'Child 2.3.1.1'],
                    ['id' => 10, 'name' => 'Child 2.3.1.1'],
                ]],
                ['id' => 12, 'name' => 'Child 2.3.2'],
                ['id' => 13, 'name' => 'Child 2.3.3'],
            ]],
            ['id' => 14, 'name' => 'Child 2.4'],
        ];

        $hierarchy = $parent->reload()->getDescendants()->toHierarchy()->toArray();
        $this->assertArraysAreEqual($expected, Category::arrayIntsKeys(Category::hmap($hierarchy, ['id', 'name'])));
    }

    protected function getDefaultTree()
    {
        $result = [
            ['id' => 1, 'name' => 'Root A'],
            ['id' => 2, 'name' => 'Root B'],
            ['id' => 3, 'name' => 'Root C', 'children' => [
                ['id' => 4, 'name' => 'C.1', 'parent_id' => 3, 'children' => [
                    ['id' => 5, 'name' => 'C.1.1'],
                    ['id' => 6, 'name' => 'C.1.2'],
                ],
                ],
                ['id' => 7, 'name' => 'C.2'],
                ['id' => 8, 'name' => 'C.3'],
            ],
            ],
            ['id' => 9, 'name' => 'Root D'],
        ];

        return $result;
    }
}

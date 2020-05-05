<?php

namespace Baum\Tests\Suite\Category;

use Baum\Tests\Suite\Models\Category;
use Baum\Tests\Suite\UnitAbstract;

class CategoryAbstract extends UnitAbstract
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->catagory_create();
    }

    protected function categories($name, $className = Category::class)
    {
        return forward_static_call_array([$className, 'where'], ['name', '=', $name])->first();
    }

    public function catagory_create()
    {
        //Category::unguard();

        Category::create(['id' => 1, 'name' => 'Root 1', 'lft' => 1, 'rgt' => 10, 'depth' => 0]);
        Category::create(['id' => 2, 'name' => 'Child 1', 'lft' => 2, 'rgt' => 3, 'depth' => 1, 'parent_id' => 1]);
        Category::create(['id' => 3, 'name' => 'Child 2', 'lft' => 4, 'rgt' => 7, 'depth' => 1, 'parent_id' => 1]);
        Category::create(['id' => 4, 'name' => 'Child 2.1', 'lft' => 5, 'rgt' => 6, 'depth' => 2, 'parent_id' => 3]);
        Category::create(['id' => 5, 'name' => 'Child 3', 'lft' => 8, 'rgt' => 9, 'depth' => 1, 'parent_id' => 1]);
        Category::create(['id' => 6, 'name' => 'Root 2', 'lft' => 11, 'rgt' => 12, 'depth' => 0]);

        //Category::reguard();

//         if (DB::connection()->getDriverName() === 'pgsql') {
//             $tablePrefix = DB::connection()->getTablePrefix();
//             $sequenceName = $tablePrefix.'categories_id_seq';
//             DB::connection()->statement('ALTER SEQUENCE '.$sequenceName.' RESTART WITH 7');
//         }
    }
}

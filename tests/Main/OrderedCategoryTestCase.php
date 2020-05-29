<?php

class OrderedCategoryTestCase extends BaumTestCase
{
    public function setUp()
    {
        parent::setUp();

        with(new CategoryMigrator())->up();
        with(new OrderedCategorySeeder())->run();
    }

    protected function categories($name, $className = 'Category')
    {
        return forward_static_call_array([$className, 'where'], ['name', '=', $name])->first();
    }
}

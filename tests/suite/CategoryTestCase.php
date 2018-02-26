<?php

class CategoryTestCase extends BaumTestCase
{
    public function setUp()
    {
        parent::setUp();

        with(new CategoryMigrator())->up();
        with(new CategorySeeder())->run();
    }

    protected function categories($name, $className = 'Category')
    {
        return forward_static_call_array([$className, 'where'], ['name', '=', $name])->first();
    }
}

<?php

class CategoryTestCase extends BaumTestCase
{
    public function setUp()
    {
        parent::setUp();

        with(new CategoryMigrator())->up();
        with(new CategorySeeder())->run();
    }
}

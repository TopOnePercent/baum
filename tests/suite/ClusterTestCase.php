<?php

class ClusterTestCase extends BaumTestCase
{
    public static function setUpBeforeClass()
    {
        with(new ClusterMigrator)->up();
    }

    public function setUp()
    {
        with(new ClusterSeeder)->run();
    }

    protected function clusters($name, $className = 'Cluster')
    {
        return forward_static_call_array([$className, 'where'], ['name', '=', $name])->first();
    }
}

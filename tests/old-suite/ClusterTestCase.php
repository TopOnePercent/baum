<?php

class ClusterTestCase extends BaumTestCase
{
    public function setUp()
    {
        parent::setUp();

        with(new ClusterMigrator())->up();
        with(new ClusterSeeder())->run();
    }

    protected function clusters($name, $className = 'Cluster')
    {
        return forward_static_call_array([$className, 'where'], ['name', '=', $name])->first();
    }
}

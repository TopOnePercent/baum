<?php

namespace Baum\Seeder;

class ClusterSeeder
{
    public function run()
    {
        Cluster::unguard();

        Cluster::create(['id' => '7461d8f5-2ea9-4788-99c4-9d0244f0bfb1', 'name' => 'Root 1', 'lft' => 1, 'rgt' => 10, 'depth' => 0]);
        Cluster::create(['id' => '5d7ce1fd-6151-46d3-a5b3-0ebb9988dc57', 'name' => 'Child 1', 'lft' => 2, 'rgt' => 3, 'depth' => 1, 'parent_id' => '7461d8f5-2ea9-4788-99c4-9d0244f0bfb1']);
        Cluster::create(['id' => '07c1fc8c-53b5-4fe7-b9c4-e09f266a455c', 'name' => 'Child 2', 'lft' => 4, 'rgt' => 7, 'depth' => 1, 'parent_id' => '7461d8f5-2ea9-4788-99c4-9d0244f0bfb1']);
        Cluster::create(['id' => '3315a297-af87-4ad3-9fa5-19785407573d', 'name' => 'Child 2.1', 'lft' => 5, 'rgt' => 6, 'depth' => 2, 'parent_id' => '07c1fc8c-53b5-4fe7-b9c4-e09f266a455c']);
        Cluster::create(['id' => '054476d2-6830-4014-a181-4de010ef7114', 'name' => 'Child 3', 'lft' => 8, 'rgt' => 9, 'depth' => 1, 'parent_id' => '7461d8f5-2ea9-4788-99c4-9d0244f0bfb1']);
        Cluster::create(['id' => '3bb62314-9e1e-49c6-a5cb-17a9ab9b1b9a', 'name' => 'Root 2', 'lft' => 11, 'rgt' => 12, 'depth' => 0]);

        Cluster::reguard();
    }

    public function nestUptoAt($node, $levels = 10, $attrs = [])
    {
        for ($i = 0; $i < $levels; $i++, $node = $new) {
            $new = Cluster::create(array_merge($attrs, ['name' => "{$node->name}.1"]));
            $new->makeChildOf($node);
        }
    }
}

class OrderedClusterSeeder
{
    public function run()
    {
        OrderedCluster::unguard();

        OrderedCluster::create(['id' => '7461d8f5-2ea9-4788-99c4-9d0244f0bfb1', 'name' => 'Root Z', 'lft' => 1, 'rgt' => 10, 'depth' => 0]);
        OrderedCluster::create(['id' => '5d7ce1fd-6151-46d3-a5b3-0ebb9988dc57', 'name' => 'Child C', 'lft' => 2, 'rgt' => 3, 'depth' => 1, 'parent_id' => '7461d8f5-2ea9-4788-99c4-9d0244f0bfb1']);
        OrderedCluster::create(['id' => '07c1fc8c-53b5-4fe7-b9c4-e09f266a455c', 'name' => 'Child G', 'lft' => 4, 'rgt' => 7, 'depth' => 1, 'parent_id' => '7461d8f5-2ea9-4788-99c4-9d0244f0bfb1']);
        OrderedCluster::create(['id' => '3315a297-af87-4ad3-9fa5-19785407573d', 'name' => 'Child G.1', 'lft' => 5, 'rgt' => 6, 'depth' => 2, 'parent_id' => '07c1fc8c-53b5-4fe7-b9c4-e09f266a455c']);
        OrderedCluster::create(['id' => '054476d2-6830-4014-a181-4de010ef7114', 'name' => 'Child A', 'lft' => 8, 'rgt' => 9, 'depth' => 1, 'parent_id' => '7461d8f5-2ea9-4788-99c4-9d0244f0bfb1']);
        OrderedCluster::create(['id' => '3bb62314-9e1e-49c6-a5cb-17a9ab9b1b9a', 'name' => 'Root A', 'lft' => 11, 'rgt' => 12, 'depth' => 0]);

        OrderedCluster::reguard();
    }
}

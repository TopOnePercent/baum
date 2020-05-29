<?php

namespace Baum\Tests\Main\Cluster;
use Baum\Tests\Main\UnitAbstract;


class ClusterColumnsTest extends UnitAbstract
{
    public function testKeyIsNonNumeric()
    {
        $root = Cluster::root();

        $this->assertTrue(is_string($root->getKey()));
        $this->assertFalse(is_numeric($root->getKey()));
    }

    public function testParentKeyIsNonNumeric()
    {
        $child1 = $this->clusters('Child 1');

        $this->assertTrue(is_string($child1->getParentId()));
        $this->assertFalse(is_numeric($child1->getParentId()));
    }
}

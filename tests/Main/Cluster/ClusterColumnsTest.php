<?php

use Baum\Tests\Main\UnitAbstract;
use Baum\Tests\Main\Models\Cluster;

class ClusterColumnsTest extends UnitAbstract
{
    protected function setUp(): void
    {
        parent::setUp();

        Cluster::create(['name' => 'A']);
        $this->root = Cluster::root();

        $this->child = Cluster::create(['name' => 'A.A']);
        $this->child->makeChildOf($this->root);
    }

    public function testKeyIsNonNumeric()
    {
        $this->assertTrue(is_string($this->root->getKey()));
        $this->assertFalse(is_numeric($this->root->getKey()));
    }

    public function testParentKeyIsNonNumeric()
    {
        $this->assertTrue(is_string($this->child->getParentId()));
        $this->assertFalse(is_numeric($this->child->getParentId()));
    }
}

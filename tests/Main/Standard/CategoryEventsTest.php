<?php

namespace Baum\Tests\Main\Standard;

use Baum\Tests\Main\Concerns\NodeModelExtensionsTestTrait;
use Baum\Tests\Main\Models\Category;
use Baum\Tests\Main\Support\PopulateData;
use Baum\Tests\Main\UnitAbstract;
use Illuminate\Support\Facades\Event;

class CategoryEventsTest extends UnitAbstract
{
    use NodeModelExtensionsTestTrait;

    public function testDeletesNode()
    {
        $node = new Category();

        try {
            $node->destroyDescendants();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail();
        }
    }

    public function testShiftsSiblingsForRestore()
    {
        $node = new Category();

        try {
            $node->shiftSiblingsForRestore();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail();
        }
    }

    public function testRestoresDescendants()
    {
        $node = new Category();

        try {
            $node->restoreDescendants();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail();
        }
    }
}

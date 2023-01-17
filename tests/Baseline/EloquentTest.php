<?php

namespace Baum\Tests\Baseline;

use Baum\Tests\Baseline\Models\BaseLineAlpha;

class EloquentTest extends UnitAbstract
{
    /** @test */
    public function recordCountTest()
    {
        factory(BaseLineAlpha::class, 50)->create();
        $this->assertEquals(BaseLineAlpha::count(), 50);
    }
}

<?php

namespace Baum\Tests\Baseline;

use Baum\Tests\Baseline\Models\BaseLineAlpha;
use Baum\Tests\Baseline\UnitAbstract;

class EloquentTest extends UnitAbstract
{

	/** @test */
    public function record_count_test()
    {
		factory(BaseLineAlpha::class, 50)->create();
		$this->assertEquals(BaseLineAlpha::count(), 50);
	}

}

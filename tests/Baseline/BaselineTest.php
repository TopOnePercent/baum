<?php

namespace Baum\Tests\Baseline;

use PHPUnit\Framework\TestCase;

class BaselineTest extends TestCase
{
    use MyTrait;

    /** @test */
    public function trueIsTrue()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function traitTest()
    {
        $this->assertTrue($this->stub());
    }
}

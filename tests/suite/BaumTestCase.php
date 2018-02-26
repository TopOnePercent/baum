<?php

class BaumTestCase extends Orchestra\Testbench\TestCase
{
    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    // protected function getEnvironmentSetUp($app)
    // {
    //     $app['config']->set('database.default', 'testing');
    //     $app['config']->set('database.connections.testing', [
    //         'driver'   => 'sqlite',
    //         'database' => ':memory:',
    //         'prefix'   => '',
    //     ]);
    // }

    public function assertArraysAreEqual($expected, $actual, $message = '')
    {
        $ex = var_export($expected, true);
        $ac = var_export($actual, true);

        return $this->assertEquals($ex, $ac, $message);
    }
}

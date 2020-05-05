<?php

namespace Baum\Tests\Suite;

use Orchestra\Testbench\TestCase;

class BaumTestCase extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
//     protected function getEnvironmentSetUp($app)
//     {
//         $config = [
//             'driver'   => 'sqlite',
//             'database' => ':memory:',
//             'prefix'   => '',
//         ];
//
//         // $config = [
//         //     'driver' => 'mysql',
//         //     'host' => 'localhost',
//         //     'username' => 'root',
//         //     'password' => '',
//         //     'database' => 'baum_testing'
//         // ];
//
//         // Setup database
//         $app['config']->set('database.default', 'default');
//         $app['config']->set('database.connections.default', $config);
//     }

    public function assertArraysAreEqual($expected, $actual, $message = '')
    {
        $ex = json_encode($expected, JSON_PRETTY_PRINT);
        $ac = json_encode($actual, JSON_PRETTY_PRINT);

        return $this->assertEquals($ex, $ac, $message);
    }

    public function assertNodesAreEqual($a, $b)
    {
        if (is_object($a)) {
            $a = $a->getAttributes();
        }

        if (is_object($b)) {
            $b = $b->getAttributes();
        }

        $a = array_only($a, ['id', 'lft', 'rgt', 'name', 'parent_id']);
        $b = array_only($b, ['id', 'lft', 'rgt', 'name', 'parent_id']);

        return $this->assertArraysAreEqual($a, $b);
    }

    protected function categories($name, $className = 'Category')
    {
        return forward_static_call_array([$className, 'where'], ['name', '=', $name])->first();
    }

    protected function debugQueries()
    {
        \DB::listen(function ($query) {
            static $count = 1;
            static $queries = [];

            if ($count == 1) {
                \Log::info('---');
            }

            $replace = function ($sql, $bindings) {
                $needle = '?';
                foreach ($bindings as $replace) {
                    $pos = strpos($sql, $needle);
                    if ($pos !== false) {
                        if ($replace === null) {
                            $replace = 'NULL';
                        }
                        $sql = substr_replace($sql, $replace, $pos, strlen($needle));
                    }
                }

                return $sql;
            };

            $result = $replace($query->sql, $query->bindings);

            $number = $count++;

            if (! isset($queries[$result])) {
                $queries[$result] = 1;
            } else {
                $queries[$result]++;
            }

            $ordinal = function ($number) {
                $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];

                if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
                    return $number.'th';
                } else {
                    return $number.$ends[$number % 10];
                }
            };

            $queryRepeatCount = '';
            if ($queries[$result] > 1) {
                $x = $queries[$result];
                $queryRepeatCount = " - {$ordinal($x)} occurrence";
            }

            echo "\n/* Query $number */";
            echo "\n/*".str_repeat('-', 256).'*/';
            echo "\n{$result}";
            // echo "\n- {$query->time}mS{$queryRepeatCount}\n";

            $backtrace = debug_backtrace();
            $result = [];
            array_walk($backtrace, function ($a, $b) use (&$result) {
                if (isset($a['file'])) {
                    if (strpos($a['file'], 'vendor') === false) {
                        if (! isset($a['class'])) {
                            $a['class'] = '';
                        }

                        $function = sprintf('%-50s', $a['class'].'#'.$a['function']);
                        $string = "$function | {$a['file']}:{$a['line']}";
                        array_push($result, $string);
                    }
                }
            });

            $result = array_reverse($result);
            echo "/*\n";
            foreach ($result as $v) {
                echo "$v\n";
            }
            echo "*/\n";
        });
    }
}

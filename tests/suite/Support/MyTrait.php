<?php

namespace Baum\Tests\Suite\Support;

trait MyTrait
{
    public function stub($data)
    {
        return $data;
    }

    /**
     * Simple function which aids in converting the tree hierarchy into something
     * more easily testable...
     *
     * @param array $nodes
     *
     * @return array
     */
    public function hierarchy(array $nodes, $preserve = null)
    {
        $output = [];

        foreach ($nodes as $node) {
            if (is_null($preserve)) {
                $output[$node['name']] = empty($node['children']) ? null : $this->hierarchy($node['children']);
            } else {
                $preserve = is_string($preserve) ? [$preserve] : $preserve;

                $current = array_only($node, $preserve);
                if (array_key_exists('children', $node)) {
                    $children = $node['children'];

                    if (count($children) > 0) {
                        $current['children'] = hmap($children, $preserve);
                    }
                }

                $output[] = $current;
            }
        }

        return $output;
    }

    public function assertArraysAreEqual($expected, $actual, $message = '')
    {
        $ex = json_encode($expected, JSON_PRETTY_PRINT);
        $ac = json_encode($actual, JSON_PRETTY_PRINT);

        return $this->assertEquals($ex, $ac, $message);
    }
}

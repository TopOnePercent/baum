<?php

namespace Baum\Tests\Main\Models;

use Baum\Node;

class Category extends Node
{
    protected $table = 'categories';

    protected $fillable = ['name'];

    public $timestamps = false;

    /**
     * Simple function which aids in converting the tree hierarchy into something
     * more easily testable...
     *
     * @param array $nodes
     *
     * @return array
     */
    public static function hmap(array $nodes, $preserve = null)
    {
        $output = [];

        foreach ($nodes as $node) {
            if (is_null($preserve)) {
                $output[$node['name']] = empty($node['children']) ? null : hmap($node['children']);
            } else {
                $preserve = is_string($preserve) ? [$preserve] : $preserve;

                $current = Arr::only($node, $preserve);
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
}

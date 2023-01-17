<?php

namespace Baum\Tests\Main\Models;

use Baum\Node;
use Illuminate\Support\Arr;

class Category extends Node
{
    protected $table = 'categories';

    protected $fillable = ['name'];

    public $timestamps = false;

    public static function categories(string $name)
    {
        return Category::where('name', $name)->first();
    }

    /**
     * Cast provided keys's values into ints. This is to wrestle with PDO driver
     * inconsistencies.
     *
     * @param array $input
     * @param mixed $keys
     *
     * @return array
     */
    public static function arrayIntsKeys(array $input, $keys = 'id')
    {
        $keys = is_string($keys) ? [$keys] : $keys;

        array_walk_recursive($input, function (&$value, $key) use ($keys) {
            if (array_search($key, $keys) !== false) {
                $value = (int) $value;
            }
        });

        return $input;
    }

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
                $output[$node['name']] = empty($node['children']) ? null : self::hmap($node['children']);
            } else {
                $preserve = is_string($preserve) ? [$preserve] : $preserve;

                $current = Arr::only($node, $preserve);
                if (array_key_exists('children', $node)) {
                    $children = $node['children'];

                    if (count($children) > 0) {
                        $current['children'] = self::hmap($children, $preserve);
                    }
                }

                $output[] = $current;
            }
        }

        return $output;
    }
}

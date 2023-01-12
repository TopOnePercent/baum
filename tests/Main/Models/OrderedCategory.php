<?php

namespace Baum\Tests\Main\Models;

class OrderedCategory extends Category
{
    protected $orderColumn = 'name';

    protected $fillable = ['name'];

    public static function categories(string $name)
    {
        return OrderedCategory::where('name', $name)->first();
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

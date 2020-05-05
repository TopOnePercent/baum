<?php

namespace Baum\Tests\Suite\Models;

use Baum\Node;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}

class ScopedCategory extends Category
{
    protected $scoped = ['company_id'];
}

class MultiScopedCategory extends Category
{
    protected $scoped = ['company_id', 'language'];

    protected $fillable = ['name', 'company_id', 'language'];
}

class OrderedCategory extends Category
{
    protected $orderColumn = 'name';

    protected $fillable = ['name'];
}

class OrderedScopedCategory extends Category
{
    protected $scoped = ['company_id'];

    protected $fillable = ['name', 'company_id'];

    protected $orderColumn = 'name';
}

class SoftCategory extends Category
{
    use SoftDeletes;

    public $timestamps = true;

    protected $dates = ['deleted_at'];
}

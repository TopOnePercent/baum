<?php

namespace Baum\Tests\Main\Models;

use Baum\Node;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cluster extends Node
{
    protected $table = 'clusters';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['name'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cluster) {
            $cluster->ensureUuid();
        });
    }

    public function ensureUuid()
    {
        if (is_null($this->getAttribute($this->getKeyName()))) {
            $this->setAttribute($this->getKeyName(), $this->generateUuid());
        }

        return $this;
    }

    protected function generateUuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0x0fff) | 0x4000,
      mt_rand(0, 0x3fff) | 0x8000,
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    }

    public static function clusters(string $name)
    {
        return Cluster::where('name', $name)->first();
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

class ScopedCluster extends Cluster
{
    protected $scoped = ['company_id'];
}

class MultiScopedCluster extends Cluster
{
    protected $scoped = ['company_id', 'language'];
}

class OrderedCluster extends Cluster
{
    protected $orderColumn = 'name';
}

class SoftCluster extends Cluster
{
    use SoftDeletes;

    public $timestamps = true;

    protected $dates = ['deleted_at'];
}

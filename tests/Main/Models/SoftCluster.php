<?php

namespace Baum\Tests\Main\Models;

class SoftCluster extends Cluster
{
    use SoftDeletes;

    public $timestamps = true;

    protected $dates = ['deleted_at'];
}

<?php

namespace Baum\Tests\Main\Models;

class MultiScopedCluster extends Cluster
{
    protected $scoped = ['company_id', 'language'];
}

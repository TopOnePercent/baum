<?php

namespace Baum\Tests\Main\Models;

class ScopedCluster extends Cluster
{
    protected $scoped = ['company_id'];
}

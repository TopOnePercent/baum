<?php

namespace Baum\Tests\Main\Models;

class OrderedCategory extends MultiScopedCategory
{
    protected $orderColumn = 'name';

    protected $fillable = ['name'];
}

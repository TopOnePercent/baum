<?php

namespace Baum\Tests\Main\Models;

use Baum\Tests\Main\Models\MultiScopedCategory;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderedCategory extends MultiScopedCategory
{
    protected $orderColumn = 'name';

    protected $fillable = ['name'];
}
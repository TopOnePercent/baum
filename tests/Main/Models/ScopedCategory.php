<?php

namespace Baum\Tests\Main\Models;

use Baum\Tests\Main\Models\Category;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScopedCategory extends Category
{
    protected $scoped = ['company_id'];
}
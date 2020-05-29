<?php

namespace Baum\Tests\Main\Models;

use  Baum\Tests\Main\Models\OrderedCategory;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderedScopedCategory extends OrderedCategory
{
    protected $scoped = ['company_id'];

    protected $fillable = ['name', 'company_id'];

    protected $orderColumn = 'name';
}
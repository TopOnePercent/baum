<?php

namespace Baum\Tests\Main\Models;

use Baum\Tests\Main\Models\ScopedCategory;
use Illuminate\Database\Eloquent\SoftDeletes;

class MultiScopedCategory extends ScopedCategory
{
    protected $scoped = ['company_id', 'language'];

    protected $fillable = ['name', 'company_id', 'language'];
}
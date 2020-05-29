<?php

namespace Baum\Tests\Main\Models;

use Baum\Tests\Main\Models\OrderedScopedCategory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoftCategory extends OrderedScopedCategory
{
    use SoftDeletes;

    public $timestamps = true;

    protected $dates = ['deleted_at'];
}
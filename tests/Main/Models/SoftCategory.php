<?php

namespace Baum\Tests\Main\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftCategory extends Category
{
    use SoftDeletes;

    public $timestamps = true;

    protected $dates = ['deleted_at'];
}

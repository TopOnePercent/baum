<?php

namespace Baum\Tests\Main\Models;

use Baum\Tests\Main\Models\Category;

class OrderedCategory extends Category
{
    protected $orderColumn = 'name';

    protected $fillable = ['name'];
}

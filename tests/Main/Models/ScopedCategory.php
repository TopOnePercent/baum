<?php

namespace Baum\Tests\Main\Models;

use Baum\Tests\Main\Models\Category;

class ScopedCategory extends Category
{
    protected $scoped = ['company_id'];
}

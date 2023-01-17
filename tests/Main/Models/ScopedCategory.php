<?php

namespace Baum\Tests\Main\Models;

class ScopedCategory extends Category
{
    protected $scoped = ['company_id'];
}

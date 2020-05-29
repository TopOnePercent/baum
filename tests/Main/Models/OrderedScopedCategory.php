<?php

namespace Baum\Tests\Main\Models;

class OrderedScopedCategory extends OrderedCategory
{
    protected $scoped = ['company_id'];

    protected $fillable = ['name', 'company_id'];

    protected $orderColumn = 'name';
}

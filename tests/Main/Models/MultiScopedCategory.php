<?php

namespace Baum\Tests\Main\Models;

class MultiScopedCategory extends ScopedCategory
{
    protected $scoped = ['company_id', 'language'];

    protected $fillable = ['name', 'company_id', 'language'];
}

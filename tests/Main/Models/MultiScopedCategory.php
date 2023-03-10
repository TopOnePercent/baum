<?php

namespace Baum\Tests\Main\Models;

class MultiScopedCategory extends Category
{
    protected $scoped = ['company_id', 'language'];

    protected $fillable = ['name', 'company_id', 'language'];

    public static function categories(string $name)
    {
        return MultiScopedCategory::where('name', $name)->first();
    }
}

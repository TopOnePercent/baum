<?php

namespace Baum\Tests\Main\Models;

class OrderedCategory extends Category
{
    protected $orderColumn = 'name';

    protected $fillable = ['name'];
}

<?php

namespace Baum\Tests\Basic\Models;

use Illuminate\Database\Eloquent\Model;
use Baum\Node;

class BasicBaum extends Node
{
    protected $fillable = ['name'];
}

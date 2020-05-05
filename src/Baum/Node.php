<?php

namespace Baum;

use Baum\Traits\NestedSet;
use Illuminate\Database\Eloquent\Model;

/**
 * Node.
 *
 * This abstract class implements Nested Set functionality. A Nested Set is a
 * smart way to implement an ordered tree with the added benefit that you can
 * select all of their descendants with a single query. Drawbacks are that
 * insertion or move operations need more complex sql queries.
 *
 * Nested sets are appropiate when you want either an ordered tree (menus,
 * commercial categories, etc.) or an efficient way of querying big trees.
 */
abstract class Node extends Model
{
    use NestedSet;
}

<?php

namespace Baum;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Contracts\ArrayableInterface;

class SetMapper
{
    /**
     * Node instance for reference.
     *
     * @var \Baum\Node
     */
    protected $node = null;

    /**
     * Children key name.
     *
     * @var string
     */
    protected $childrenKeyName = 'children';

    /**
     * Create a new \Baum\SetBuilder class instance.
     *
     * @param \Baum\Node $node
     *
     * @return void
     */
    public function __construct($node, $childrenKeyName = 'children')
    {
        $this->node = $node;

        $this->childrenKeyName = $childrenKeyName;
    }

    /**
     * Maps a tree structure into the database. Unguards & wraps in transaction.
     *
     * @param   array|\Illuminate\Support\Contracts\ArrayableInterface
     *
     * @return bool
     */
    public function map($nodeList)
    {
        $self = $this;

        return $this->wrapInTransaction(function () use ($self, $nodeList) {
            forward_static_call([get_class($self->node), 'unguard']);
            $result = $self->mapTree($nodeList);
            forward_static_call([get_class($self->node), 'reguard']);

            return $result;
        });
    }

    /**
     * Maps a tree structure into the database without unguarding nor wrapping
     * inside a transaction.
     *
     * @param   array|\Illuminate\Support\Contracts\ArrayableInterface
     *
     * @return bool
     */
    public function mapTree($nodeList)
    {
        $affectedKeys = [];
        $tree = $nodeList instanceof ArrayableInterface ? $nodeList->toArray() : $nodeList;

        $result = $this->mapTreeRecursive($tree, $this->node, $affectedKeys);

        if ($result && count($affectedKeys) > 0) {
            $this->deleteUnaffected($affectedKeys);
        }

        return $result;
    }

    /**
     * Returns the children key name to use on the mapping array.
     *
     * @return string
     */
    public function getChildrenKeyName()
    {
        return $this->childrenKeyName;
    }

    /**
     * Maps a tree structure into the database.
     *
     * @param array $tree
     * @param mixed $parent
     *
     * @return bool
     */
    protected function mapTreeRecursive(array $tree, $parent = null, &$affectedKeys = [])
    {
        // For every attribute entry: We'll need to instantiate a new node either
        // from the database (if the primary key was supplied) or a new instance. Then,
        // append all the remaining data attributes (including the `parent_id` if
        // present) and save it. Finally, tail-recurse performing the same
        // operations for any child node present. Setting the `parent_id` property at
        // each level will take care of the nesting work for us.
        foreach ($tree as $attributes) {
            // Find or create the node
            $node = $this->firstOrNew($this->getSearchAttributes($attributes));

            // Set the parent and data for the node
            $data = $this->getDataAttributes($attributes);
            if ($parent) {
                $data[$node->getParentColumnName()] = $parent->getKey();
            }
            $node->fill($data);

            if (! $node->save()) {
                throw new \Exception('Unable to save node');
            }

            if (! $node->isRoot()) {
                $node->makeLastChildOf($node->parent);
            }

            $affectedKeys[] = $node->getKey();

            if (array_key_exists($this->getChildrenKeyName(), $attributes)) {
                $children = $attributes[$this->getChildrenKeyName()];

                if (count($children) > 0) {
                    $this->mapTreeRecursive($children, $node, $affectedKeys);
                }
            }
        }

        return true;
    }

    protected function getSearchAttributes($attributes)
    {
        $searchable = [$this->node->getKeyName()];

        return Arr::only($attributes, $searchable);
    }

    protected function getDataAttributes($attributes)
    {
        $exceptions = [$this->node->getKeyName(), $this->getChildrenKeyName()];

        return Arr::except($attributes, $exceptions);
    }

    protected function firstOrNew($attributes)
    {
        $className = get_class($this->node);

        if (count($attributes) === 0) {
            return new $className();
        }

        return forward_static_call([$className, 'firstOrNew'], $attributes);
    }

    protected function pruneScope()
    {
        if ($this->node->exists) {
            return $this->node->descendants();
        }

        return $this->node->newNestedSetQuery();
    }

    protected function deleteUnaffected($keys = [])
    {
        return $this->pruneScope()->whereNotIn($this->node->getKeyName(), $keys)->delete();
    }

    protected function wrapInTransaction(Closure $callback)
    {
        return $this->node->getConnection()->transaction($callback);
    }
}

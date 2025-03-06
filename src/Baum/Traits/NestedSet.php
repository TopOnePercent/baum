<?php

namespace Baum\Traits;

use Baum\Extensions\Eloquent\Collection;
use Baum\Extensions\Query\Builder as QueryBuilder;
use Baum\Move;
use Baum\SetBuilder;
use Baum\SetMapper;
use Baum\SetValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletingScope;

trait NestedSet
{
    /**
     * Column name to store the reference to parent's node.
     *
     * @var string
     */
    protected $parentColumn = 'parent_id';

    /**
     * Column name for left index.
     *
     * @var string
     */
    protected $leftColumn = 'lft';

    /**
     * Column name for right index.
     *
     * @var string
     */
    protected $rightColumn = 'rgt';

    /**
     * Column name for depth field.
     *
     * @var string
     */
    protected $depthColumn = 'depth';

    /**
     * Column to perform the default sorting.
     *
     * @var string
     */
    protected $orderColumn;

    /**
     * Guard NestedSet fields from mass-assignment.
     *
     * @var array
     */
    protected static $_guarded = ['id', 'parent_id', 'lft', 'rgt', 'depth'];

    /**
     * Indicates whether we should move to a new parent.
     *
     * @var int
     */
    protected $newParentId;

    /**
     * Columns which restrict what we consider our Nested Set list.
     *
     * @var array
     */
    protected $scoped = [];

    /**
     * Fire events on descendant nodes when deleting.
     *
     * @var bool
     */
    protected $fireDescendantDeleteEvents = false;

    /**
     * The "booting" method of the model.
     *
     * We'll use this method to register event listeners on a Node instance as
     * suggested in the beta documentation...
     *
     * TODO:
     *
     *    - Find a way to avoid needing to declare the called methods "public"
     *    as registering the event listeners *inside* this methods does not give
     *    us an object context.
     *
     * Events:
     *
     *    1. "creating": Before creating a new Node we'll assign a default value
     *    for the left and right indexes.
     *
     *    2. "saving": Before saving, we'll perform a check to see if we have to
     *    move to another parent.
     *
     *    3. "saved": Move to the new parent after saving if needed and re-set
     *    depth.
     *
     *    4. "deleting": Before delete we should prune all children and update
     *    the left and right indexes for the remaining nodes.
     *
     *    5. (optional) "restoring": Before a soft-delete node restore operation,
     *    shift its siblings.
     *
     *    6. (optional) "restore": After having restored a soft-deleted node,
     *    restore all of its descendants.
     *
     * @return void
     */
    protected static function bootNestedSet()
    {
        static::retrieved(function ($node): void {
            $node->setGuards();
        });

        static::creating(function ($node): void {
            $node->setDefaultLeftAndRight();
            $node->setGuards();
        });

        static::saved(function ($node): void {
            $node->moveToNewParent();
            $node->setDepth();
        });

        static::deleting(function ($node): void {
            $node->destroyDescendants();
        });

        if (static::softDeletesEnabled()) {
            static::restoring(function ($node): void {
                $node->shiftSiblingsForRestore();
            });

            static::restored(function ($node): void {
                $node->restoreDescendants();
            });
        }
    }

    /**
     * Get the parent column name.
     *
     * @return string
     */
    public function getParentColumnName()
    {
        return $this->parentColumn;
    }

    /**
     * Get the table qualified parent column name.
     */
    public function getQualifiedParentColumnName(): string
    {
        return $this->getTable() . '.' . $this->getParentColumnName();
    }

    /**
     * Get the value of the models "parent_id" field.
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->getAttribute($this->getparentColumnName());
    }

    /**
     * Get the "left" field column name.
     *
     * @return string
     */
    public function getLeftColumnName()
    {
        return $this->leftColumn;
    }

    /**
     * Get the table qualified "left" field column name.
     */
    public function getQualifiedLeftColumnName(): string
    {
        return $this->getTable() . '.' . $this->getLeftColumnName();
    }

    /**
     * Get the value of the model's "left" field.
     */
    public function getLeft(): int
    {
        return (int) $this->getAttribute($this->getLeftColumnName());
    }

    /**
     * Get the "right" field column name.
     *
     * @return string
     */
    public function getRightColumnName()
    {
        return $this->rightColumn;
    }

    /**
     * Get the table qualified "right" field column name.
     */
    public function getQualifiedRightColumnName(): string
    {
        return $this->getTable() . '.' . $this->getRightColumnName();
    }

    /**
     * Get the value of the model's "right" field.
     */
    public function getRight(): int
    {
        return (int) $this->getAttribute($this->getRightColumnName());
    }

    /**
     * Get the "depth" field column name.
     *
     * @return string
     */
    public function getDepthColumnName()
    {
        return $this->depthColumn;
    }

    /**
     * Get the table qualified "depth" field column name.
     */
    public function getQualifiedDepthColumnName(): string
    {
        return $this->getTable() . '.' . $this->getDepthColumnName();
    }

    /**
     * Get the model's "depth" value.
     *
     * @return int
     */
    public function getDepth()
    {
        return $this->getAttribute($this->getDepthColumnName());
    }

    /**
     * Get the "order" field column name.
     *
     * @return string
     */
    public function getOrderColumnName()
    {
        return is_null($this->orderColumn) ? $this->getLeftColumnName() : $this->orderColumn;
    }

    /**
     * Get the table qualified "order" field column name.
     */
    public function getQualifiedOrderColumnName(): string
    {
        return $this->getTable() . '.' . $this->getOrderColumnName();
    }

    /**
     * Get the model's "order" value.
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->getAttribute($this->getOrderColumnName());
    }

    /**
     * Get the column names which define our scope.
     */
    public function getScopedColumns(): array
    {
        return (array) $this->scoped;
    }

    /**
     * Get the qualified column names which define our scope.
     *
     * @return array
     */
    public function getQualifiedScopedColumns()
    {
        if (! $this->isScoped()) {
            return $this->getScopedColumns();
        }

        $prefix = $this->getTable() . '.';

        return array_map(function (string $c) use ($prefix): string {
            return $prefix . $c;
        }, $this->getScopedColumns());
    }

    /**
     * Returns wether this particular node instance is scoped by certain fields
     * or not.
     */
    public function isScoped(): bool
    {
        return count($this->getScopedColumns()) > 0;
    }

    /**
     * Parent relation (self-referential) 1-1.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(get_class($this), $this->getParentColumnName());
    }

    /**
     * Children relation (self-referential) 1-N.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(get_class($this), $this->getParentColumnName())
                    ->orderBy($this->getOrderColumnName());
    }

    /**
     * Get a new "scoped" query builder for the Node's model.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newNestedSetQuery()
    {
        $builder = $this->newQuery()->orderBy($this->getQualifiedOrderColumnName());

        if ($this->isScoped()) {
            foreach ($this->scoped as $scopeFld) {
                $builder->where($scopeFld, '=', $this->$scopeFld);
            }
        }

        return $builder;
    }

    /**
     * Overload new Collection.
     *
     *
     */
    public function newCollection(array $models = []): \Baum\Extensions\Eloquent\Collection
    {
        return new Collection($models);
    }

    /**
     * Get all of the nodes from the database.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function all($columns = ['*'])
    {
        $instance = new static();

        return $instance->newQuery()
                        ->orderBy($instance->getQualifiedOrderColumnName())
                        ->get($columns);
    }

    /**
     * Returns the first root node.
     *
     * @return NestedSet
     */
    public static function root()
    {
        return static::roots()->first();
    }

    /**
     * Static query scope. Returns a query scope with all root nodes.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function roots()
    {
        $instance = new static();

        return $instance->newQuery()
                        ->whereNull($instance->getParentColumnName())
                        ->orderBy($instance->getQualifiedOrderColumnName());
    }

    /**
     * Static query scope. Returns a query scope with all nodes which are at
     * the end of a branch.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function allLeaves()
    {
        $instance = new static();

        $grammar = $instance->getConnection()->getQueryGrammar();

        $rgtCol = $grammar->wrap($instance->getQualifiedRightColumnName());
        $lftCol = $grammar->wrap($instance->getQualifiedLeftColumnName());

        return $instance->newQuery()
                        ->whereRaw($rgtCol . ' - ' . $lftCol . ' = 1')
                        ->orderBy($instance->getQualifiedOrderColumnName());
    }

    /**
     * Static query scope. Returns a query scope with all nodes which are at
     * the middle of a branch (not root and not leaves).
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function allTrunks()
    {
        $instance = new static();

        $grammar = $instance->getConnection()->getQueryGrammar();

        $rgtCol = $grammar->wrap($instance->getQualifiedRightColumnName());
        $lftCol = $grammar->wrap($instance->getQualifiedLeftColumnName());

        return $instance->newQuery()
                        ->whereNotNull($instance->getParentColumnName())
                        ->whereRaw($rgtCol . ' - ' . $lftCol . ' != 1')
                        ->orderBy($instance->getQualifiedOrderColumnName());
    }

    /**
     * Checks wether the underlying Nested Set structure is valid.
     *
     * @return bool
     */
    public static function isValidNestedSet()
    {
        $validator = new SetValidator(new static());

        return $validator->passes();
    }

    /**
     * Rebuilds the structure of the current Nested Set.
     */
    public static function rebuild(): void
    {
        $builder = new SetBuilder(new static());
        $builder->rebuild();
    }

    /**
     * Maps the provided tree structure into the database.
     *
     * @param $nodeList array|\Illuminate\Contracts\Support\Arrayable
     *
     * @return bool
     */
    public static function buildTree($nodeList)
    {
        return with(new static())->makeTree($nodeList);
    }

    /**
     * Query scope which extracts a certain node object from the current query
     * expression.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithoutNode($query, $node)
    {
        return $query->where($node->getKeyName(), '!=', $node->getKey());
    }

    /**
     * Extracts current node (self) from current query expression.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithoutSelf($query)
    {
        return $this->scopeWithoutNode($query, $this);
    }

    /**
     * Extracts first root (from the current node p-o-v) from current query
     * expression.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithoutRoot($query)
    {
        return $this->scopeWithoutNode($query, $this->getRoot());
    }

    /**
     * Provides a depth level limit for the query.
     *
     * @param   query   \Illuminate\Database\Query\Builder
     * @param   limit   integer
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeLimitDepth($query, $limit)
    {
        $depth = $this->exists ? $this->getDepth() : $this->getLevel();
        $max = $depth + $limit;
        $scopes = [$depth, $max];

        return $query->whereBetween($this->getDepthColumnName(), [min($scopes), max($scopes)]);
    }

    /**
     * Returns true if this is a root node.
     */
    public function isRoot(): bool
    {
        return ! $this->getParentId();
    }

    /**
     * Returns true if this is a leaf node (end of a branch).
     */
    public function isLeaf(): bool
    {
        return $this->exists && ($this->getRight() - $this->getLeft() == 1);
    }

    /**
     * Returns true if this is a trunk node (not root or leaf).
     */
    public function isTrunk(): bool
    {
        return ! $this->isRoot() && ! $this->isLeaf();
    }

    /**
     * Returns true if this is a child node.
     */
    public function isChild(): bool
    {
        return ! $this->isRoot();
    }

    /**
     * Returns the root node starting at the current node.
     *
     * @return NestedSet
     */
    public function getRoot()
    {
        if ($this->exists) {
            return $this->ancestorsAndSelf()->whereNull($this->getParentColumnName())->first();
        } else {
            $parentId = $this->getParentId();

            if (! $parentId && $currentParent = static::find($parentId)) {
                return $currentParent->getRoot();
            } else {
                return $this;
            }
        }
    }

    /**
     * Instance scope which targes all the ancestor chain nodes including
     * the current one.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function ancestorsAndSelf()
    {
        return $this->newNestedSetQuery()
                    ->where($this->getLeftColumnName(), '<=', $this->getLeft())
                    ->where($this->getRightColumnName(), '>=', $this->getRight());
    }

    /**
     * Get all the ancestor chain from the database including the current node.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestorsAndSelf($columns = ['*'])
    {
        return $this->ancestorsAndSelf()->get($columns);
    }

    /**
     * Get all the ancestor chain from the database including the current node
     * but without the root node.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestorsAndSelfWithoutRoot($columns = ['*'])
    {
        return $this->ancestorsAndSelf()->withoutRoot()->get($columns);
    }

    /**
     * Instance scope which targets all the ancestor chain nodes excluding
     * the current one.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function ancestors()
    {
        return $this->ancestorsAndSelf()->withoutSelf();
    }

    /**
     * Get all the ancestor chain from the database excluding the current node.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestors($columns = ['*'])
    {
        return $this->ancestors()->get($columns);
    }

    /**
     * Get all the ancestor chain from the database excluding the current node
     * and the root node (from the current node's perspective).
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestorsWithoutRoot($columns = ['*'])
    {
        return $this->ancestors()->withoutRoot()->get($columns);
    }

    /**
     * Instance scope which targets all children of the parent, including self.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function siblingsAndSelf()
    {
        return $this->newNestedSetQuery()
                    ->where($this->getParentColumnName(), $this->getParentId());
    }

    /**
     * Get all children of the parent, including self.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSiblingsAndSelf($columns = ['*'])
    {
        return $this->siblingsAndSelf()->get($columns);
    }

    /**
     * Instance scope targeting all children of the parent, except self.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function siblings()
    {
        return $this->siblingsAndSelf()->withoutSelf();
    }

    /**
     * Return all children of the parent, except self.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSiblings($columns = ['*'])
    {
        return $this->siblings()->get($columns);
    }

    /**
     * Instance scope targeting all of its nested children which do not have
     * children.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function leaves()
    {
        $grammar = $this->getConnection()->getQueryGrammar();

        $rgtCol = $grammar->wrap($this->getQualifiedRightColumnName());
        $lftCol = $grammar->wrap($this->getQualifiedLeftColumnName());

        return $this->descendants()
                    ->whereRaw($rgtCol . ' - ' . $lftCol . ' = 1');
    }

    /**
     * Return all of its nested children which do not have children.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLeaves($columns = ['*'])
    {
        return $this->leaves()->get($columns);
    }

    /**
     * Instance scope targeting all of its nested children which are between the
     * root and the leaf nodes (middle branch).
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function trunks()
    {
        $grammar = $this->getConnection()->getQueryGrammar();

        $rgtCol = $grammar->wrap($this->getQualifiedRightColumnName());
        $lftCol = $grammar->wrap($this->getQualifiedLeftColumnName());

        return $this->descendants()
                    ->whereNotNull($this->getQualifiedParentColumnName())
                    ->whereRaw($rgtCol . ' - ' . $lftCol . ' != 1');
    }

    /**
     * Return all of its nested children which are trunks.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTrunks($columns = ['*'])
    {
        return $this->trunks()->get($columns);
    }

    /**
     * Scope targeting itself and all of its nested children.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function descendantsAndSelf()
    {
        return $this->newNestedSetQuery()
                    ->where($this->getLeftColumnName(), '>=', $this->getLeft())
                    ->where($this->getLeftColumnName(), '<', $this->getRight());
    }

    /**
     * Retrieve all nested children an self.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDescendantsAndSelf($columns = ['*'])
    {
        if (is_array($columns)) {
            return $this->descendantsAndSelf()->get($columns);
        }

        $arguments = func_get_args();
        $limit = intval(array_shift($arguments));
        $columns = array_shift($arguments) ?: ['*'];

        return $this->descendantsAndSelf()->limitDepth($limit)->get($columns);
    }

    /**
     * Retrieve all other nodes at the same depth,.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOthersAtSameDepth()
    {
        return $this->newNestedSetQuery()
                    ->where($this->getDepthColumnName(), '=', $this->getDepth())
                    ->withoutSelf();
    }

    /**
     * Set of all children & nested children.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function descendants()
    {
        return $this->descendantsAndSelf()->withoutSelf();
    }

    /**
     * Retrieve all of its children & nested children.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDescendants($columns = ['*'])
    {
        if (is_array($columns)) {
            return $this->descendants()->get($columns);
        }

        $arguments = func_get_args();

        $limit = intval(array_shift($arguments));
        $columns = array_shift($arguments) ?: ['*'];

        return $this->descendants()->limitDepth($limit)->get($columns);
    }

    /**
     * Set of "immediate" descendants (aka children), alias for the children relation.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function immediateDescendants()
    {
        return $this->children();
    }

    /**
     * Retrive all of its "immediate" descendants.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getImmediateDescendants($columns = ['*'])
    {
        return $this->children()->get($columns);
    }

    /**
     * Returns the level of this node in the tree.
     * Root level is 0.
     *
     * @return int
     */
    public function getLevel()
    {
        if (! $this->getParentId()) {
            return 0;
        }

        return $this->computeLevel();
    }

    /**
     * Returns true if node is a direct descendant of $other.
     *
     * @param NestedSet
     */
    public function isChildOf($other): bool
    {
        //TODO ids that are really a string should be compared as such as intval wont always return a correct value for a non numeric string based ID
        return
            intval($this->parent_id) === intval($other->id) &&
            $this->inSameScope($other);
    }

    /**
     * Returns true if node is a descendant.
     *
     * @param NestedSet
     */
    public function isDescendantOf($other): bool
    {
        return
            $this->getLeft() > $other->getLeft() &&
            $this->getLeft() < $other->getRight() &&
            $this->inSameScope($other);
    }

    /**
     * Returns true if node is self or a descendant.
     *
     * @param NestedSet
     */
    public function isSelfOrDescendantOf($other): bool
    {
        return
            $this->getLeft() >= $other->getLeft() &&
            $this->getLeft() < $other->getRight() &&
            $this->inSameScope($other);
    }

    /**
     * Returns true if node is an ancestor.
     *
     * @param NestedSet
     */
    public function isAncestorOf($other): bool
    {
        return
            $this->getLeft() < $other->getLeft() &&
            $this->getRight() > $other->getLeft() &&
            $this->inSameScope($other);
    }

    /**
     * Returns true if node is self or an ancestor.
     *
     * @param NestedSet
     */
    public function isSelfOrAncestorOf($other): bool
    {
        return
            $this->getLeft() <= $other->getLeft() &&
            $this->getRight() > $other->getLeft() &&
            $this->inSameScope($other);
    }

    /**
     * Returns the first sibling to the left.
     *
     * @return NestedSet
     */
    public function getLeftSibling()
    {
        return $this->siblings()
                    ->where($this->getLeftColumnName(), '<', $this->getLeft())
                    ->orderBy($this->getOrderColumnName(), 'desc')
                    ->get()
                    ->last();
    }

    /**
     * Returns the first sibling to the right.
     *
     * @return NestedSet
     */
    public function getRightSibling()
    {
        return $this->siblings()
                    ->where($this->getLeftColumnName(), '>', $this->getLeft())
                    ->first();
    }

    /**
     * Find the left sibling and move to left of it.
     *
     * @return \Baum\Node
     */
    public function moveLeft()
    {
        return $this->moveToLeftOf($this->getLeftSibling());
    }

    /**
     * Find the right sibling and move to the right of it.
     *
     * @return \Baum\Node
     */
    public function moveRight()
    {
        return $this->moveToRightOf($this->getRightSibling());
    }

    /**
     * Move to the node to the left of ...
     *
     * @return \Baum\Node
     */
    public function moveToLeftOf($node)
    {
        return $this->moveTo($node, 'left');
    }

    /**
     * Move to the node to the right of ...
     *
     * @return \Baum\Node
     */
    public function moveToRightOf($node)
    {
        return $this->moveTo($node, 'right');
    }

    /**
     * Alias for moveToRightOf.
     *
     * @return \Baum\Node
     */
    public function makeNextSiblingOf($node)
    {
        return $this->moveToRightOf($node);
    }

    /**
     * Alias for moveToRightOf.
     *
     * @return \Baum\Node
     */
    public function makeSiblingOf($node)
    {
        return $this->moveToRightOf($node);
    }

    /**
     * Alias for moveToLeftOf.
     *
     * @return \Baum\Node
     */
    public function makePreviousSiblingOf($node)
    {
        return $this->moveToLeftOf($node);
    }

    /**
     * Make the node a child of ...
     *
     * @return \Baum\Node
     */
    public function makeChildOf($node)
    {
        return $this->moveTo($node, 'child');
    }

    /**
     * Add a child node to a node (a more OO method than makeChildOf).
     *
     * @param [type] $node [description]
     */
    public function addChild($node)
    {
        $parentIdKey = $this->getparentColumnName();

        $node->$parentIdKey = $this->getKey();

        return $node->save();
    }

    /**
     * Make the node the first child of ...
     *
     * @return \Baum\Node
     */
    public function makeFirstChildOf($node)
    {
        if ($node->children()->count() == 0) {
            return $this->makeChildOf($node);
        }

        return $this->moveToLeftOf($node->children()->first());
    }

    /**
     * Make the node the last child of ...
     *
     * @return \Baum\Node
     */
    public function makeLastChildOf($node)
    {
        return $this->makeChildOf($node);
    }

    /**
     * Make current node a root node.
     *
     * @return \Baum\Node
     */
    public function makeRoot()
    {
        return $this->moveTo($this, 'root');
    }

    /**
     * Equals?
     *
     * @param \Baum\Node
     */
    public function equals($node): bool
    {
        return $this == $node;
    }

    /**
     * Checkes if the given node is in the same scope as the current one.
     *
     * @param \Baum\Node
     */
    public function inSameScope($other): bool
    {
        foreach ($this->getScopedColumns() as $fld) {
            if ($this->$fld != $other->$fld) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks wether the given node is a descendant of itself. Basically, whether
     * its in the subtree defined by the left and right indices.
     *
     * @param \Baum\Node
     */
    public function insideSubtree($node): bool
    {
        return
            $this->getLeft() >= $node->getLeft() &&
            $this->getLeft() <= $node->getRight() &&
            $this->getRight() >= $node->getLeft() &&
            $this->getRight() <= $node->getRight();
    }

    /**
     * Sets default values for left and right fields.
     */
    public function setDefaultLeftAndRight(): void
    {
        $withHighestRight = $this->newNestedSetQuery()->reOrderBy($this->getRightColumnName(), 'desc')->take(1)->sharedLock()->first();

        $maxRgt = 0;
        if (! is_null($withHighestRight)) {
            $maxRgt = $withHighestRight->getRight();
        }

        $this->setAttribute($this->getLeftColumnName(), $maxRgt + 1);
        $this->setAttribute($this->getRightColumnName(), $maxRgt + 2);
    }

    /**
     * Set guards on node, called when retrieved or creating.
     */
    public function setGuards(): void
    {
        $this->guarded = array_merge(static::$_guarded, $this->guarded);
    }

    /**
     * Move to the new parent if appropiate.
     *
     * @return void
     */
    private function moveToNewParent()
    {
        $parentColumnKey = $this->getParentColumnName();

        $oldParentId = $this->original[$parentColumnKey] ?? null;

        $newParentId = $this->attributes[$parentColumnKey] ?? null;

        // Should we actually move?
        if ($oldParentId == $newParentId) {
            return;
        }

        if (! $newParentId) {
            return $this->makeRoot();
        } elseif ($oldParentId != $newParentId) {
            return $this->makeChildOf($newParentId);
        }
    }

    /**
     * Sets the depth attribute.
     *
     * @return \Baum\Node
     */
    private function setDepth()
    {
        $self = $this;

        $this->getConnection()->transaction(function () use ($self): void {
            $self->reload();
            $level = $self->getLevel();
            $self->newNestedSetQuery()->where($self->getKeyName(), '=', $self->getKey())->update([$self->getDepthColumnName() => $level]);
            $self->setAttribute($self->getDepthColumnName(), $level);
        });

        return $this;
    }

    /**
     * Sets the depth attribute for the current node and all of its descendants.
     *
     * @return \Baum\Node
     */
    public function setDepthWithSubtree()
    {
        $self = $this;

        $this->getConnection()->transaction(function () use ($self): void {
            $self->reload();

            $self->descendantsAndSelf()->select($self->getKeyName())->lockForUpdate()->get();

            $oldDepth = is_null($self->getDepth()) ? 0 : $self->getDepth();
            $newDepth = $self->getLevel();

            $self->newNestedSetQuery()->where($self->getKeyName(), '=', $self->getKey())->update([$self->getDepthColumnName() => $newDepth]);
            $self->setAttribute($self->getDepthColumnName(), $newDepth);

            $diff = $newDepth - $oldDepth;
            if (! $self->isLeaf() && $diff != 0) {
                $self->descendants()->increment($self->getDepthColumnName(), $diff);
            }
        });

        return $this;
    }

    /**
     * Prunes a branch off the tree, shifting all the elements on the right
     * back to the left so the counts work.
     *
     * @return void;
     */
    public function destroyDescendants(): void
    {
        if ($this->getRight() === 0 || $this->getLeft() === 0) {
            return;
        }

        $self = $this;

        $this->getConnection()->transaction(function () use ($self): void {
            $self->reload();

            $lftCol = $self->getLeftColumnName();
            $rgtCol = $self->getRightColumnName();
            $lft = $self->getLeft();
            $rgt = $self->getRight();

            // Apply a lock to the rows which fall past the deletion point
            $self->newNestedSetQuery()
                ->where($lftCol, '>=', $lft)
                ->select($self->getKeyName())
                ->lockForUpdate()
                ->get();

            // Prune children, optionally one by one to file deleting / deleted events
            $query = $self->newNestedSetQuery()->where($lftCol, '>', $lft)->where($rgtCol, '<', $rgt);

            if (! $this->fireDescendantDeleteEvents) {
                $query->delete();
            } else {
                $query->each(function ($node): void {
                    $node->delete();
                });
            }

            // Update left and right indexes for the remaining nodes
            $diff = $rgt - $lft + 1;

            $self->newNestedSetQuery()->where($lftCol, '>', $rgt)->decrement($lftCol, $diff);
            $self->newNestedSetQuery()->where($rgtCol, '>', $rgt)->decrement($rgtCol, $diff);
        });
    }

    /**
     * "Makes room" for the the current node between its siblings.
     */
    public function shiftSiblingsForRestore(): void
    {
        if ($this->getRight() === 0 || $this->getLeft() === 0) {
            return;
        }

        $self = $this;

        $this->getConnection()->transaction(function () use ($self): void {
            $lftCol = $self->getLeftColumnName();
            $rgtCol = $self->getRightColumnName();
            $lft = $self->getLeft();
            $rgt = $self->getRight();

            $diff = $rgt - $lft + 1;

            $self->newNestedSetQuery()->where($lftCol, '>=', $lft)->increment($lftCol, $diff);
            $self->newNestedSetQuery()->where($rgtCol, '>=', $lft)->increment($rgtCol, $diff);
        });
    }

    /**
     * Restores all of the current node's descendants.
     */
    public function restoreDescendants(): void
    {
        if ($this->getRight() === 0 || $this->getLeft() === 0) {
            return;
        }

        $self = $this;

        $this->getConnection()->transaction(function () use ($self): void {
            $self->newNestedSetQuery()
             ->withTrashed()
             ->where($self->getLeftColumnName(), '>', $self->getLeft())
             ->where($self->getRightColumnName(), '<', $self->getRight())
             ->update([
                 $self->getDeletedAtColumn() => null,
                 $self->getUpdatedAtColumn() => $self->{$self->getUpdatedAtColumn()},
             ]);
        });
    }

    /**
     * Return an key-value array indicating the node's depth with $seperator.
     */
    public static function getNestedList($column, $key = null, $seperator = ' ', $symbol = ''): array
    {
        $instance = new static();

        $key = $key ?: $instance->getKeyName();
        $depthColumn = $instance->getDepthColumnName();

        $nodes = $instance->newNestedSetQuery()->get()->toArray();

        return array_combine(array_map(function (array $node) use ($key) {
            return $node[$key];
        }, $nodes), array_map(function (array $node) use ($seperator, $depthColumn, $column, $symbol): string {
            return str_repeat($seperator, $node[$depthColumn]) . $symbol . $node[$column];
        }, $nodes));
    }

    /**
     * Maps the provided tree structure into the database using the current node
     * as the parent. The provided tree structure will be inserted/updated as the
     * descendancy subtree of the current node instance.
     *
     * @param $nodeList array|\Illuminate\Contracts\Support\Arrayable
     *
     * @return bool
     */
    public function makeTree($nodeList)
    {
        $mapper = new SetMapper($this);

        return $mapper->map($nodeList);
    }

    /**
     * Main move method. Here we handle all node movements with the corresponding
     * lft/rgt index updates.
     *
     * @param Baum\Node|int $target
     * @param string        $position
     *
     * @return \Baum\Node
     */
    protected function moveTo($target, $position)
    {
        return Move::to($this, $target, $position);
    }

    /**
     * Compute current node level. If could not move past ourseleves return
     * our ancestor count, otherwhise get the first parent level + the computed
     * nesting.
     *
     * @return int
     */
    protected function computeLevel()
    {
        [$node, $nesting] = $this->determineDepth($this);

        if ($node->equals($this)) {
            return $this->ancestors()->count();
        }

        return $node->getLevel() + $nesting;
    }

    /**
     * Return an array with the last node we could reach and its nesting level.
     *
     * @param Baum\Node $node
     * @param int       $nesting
     */
    protected function determineDepth($node, $nesting = 0): array
    {
        // Traverse back up the ancestry chain and add to the nesting level count
        while ($parent = $node->parent()->first()) {
            $nesting++;
            $node = $parent;
        }

        return [$node, $nesting];
    }

    /**
     * Reloads the model from the database.
     *
     * @throws ModelNotFoundException
     *
     * @return \Baum\Node
     */
    public function reload()
    {
        if ($this->exists || ($this->areSoftDeletesEnabled() && $this->trashed())) {
            $fresh = $this->getFreshInstance();
            if (is_null($fresh)) {
                throw with(new ModelNotFoundException())->setModel(get_called_class());
            }

            // Copy deleted_at attribute from current node to stop it
            // being un-deleted when reloaded
            $freshAttributes = $fresh->getAttributes();
            $currentAttributes = $this->getAttributes();
            if (isset($currentAttributes['deleted_at'])) {
                $freshAttributes['deleted_at'] = $currentAttributes['deleted_at'];
            }

            $this->setRawAttributes($freshAttributes, true);
            $this->setRelations($fresh->getRelations());
            $this->exists = $fresh->exists;
        } else {
            // Revert changes if model is not persisted
            $this->attributes = $this->original;
        }

        return $this;
    }

    /**
     * Get the observable event names.
     */
    public function getObservableEvents(): array
    {
        return array_merge(['moving', 'moved'], parent::getObservableEvents());
    }

    /**
     * Register a moving model event with the dispatcher.
     *
     * @param Closure|string $callback
     */
    public static function moving($callback, $priority = 0): void
    {
        static::registerModelEvent('moving', $callback, $priority);
    }

    /**
     * Register a moved model event with the dispatcher.
     *
     * @param Closure|string $callback
     */
    public static function moved($callback, $priority = 0): void
    {
        static::registerModelEvent('moved', $callback, $priority);
    }

    /**
     * Get a new query builder instance for the connection.
     */
    protected function newBaseQueryBuilder(): \Baum\Extensions\Query\Builder
    {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

        return new QueryBuilder($conn, $grammar, $conn->getPostProcessor());
    }

    /**
     * Returns a fresh instance from the database.
     *
     * @return \Baum\Node
     */
    protected function getFreshInstance()
    {
        if ($this->areSoftDeletesEnabled()) {
            return static::withTrashed()->find($this->getKey());
        }

        return static::find($this->getKey());
    }

    /**
     * Returns wether soft delete functionality is enabled on the model or not.
     *
     * @return bool
     */
    public function areSoftDeletesEnabled()
    {
        // To determine if there's a global soft delete scope defined we must
        // first determine if there are any, to workaround a non-existent key error.
        $globalScopes = $this->getGlobalScopes();

        if (count($globalScopes) === 0) {
            return false;
        }

        // Now that we're sure that the calling class has some kind of global scope
        // we check for the SoftDeletingScope existance
        return static::hasGlobalScope(new SoftDeletingScope());
    }

    /**
     * Static method which returns wether soft delete functionality is enabled
     * on the model.
     *
     * @return bool
     */
    public static function softDeletesEnabled()
    {
        return with(new static())->areSoftDeletesEnabled();
    }
}

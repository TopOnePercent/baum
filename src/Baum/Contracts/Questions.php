<?php

namespace Baum\Contracts;

interface Questions
{
    public function isRoot();

    public function isLeaf();

    public function isChild();

    public function isChildOf($other);

    public function isDescendantOf($other);

    public function isSelfOrDescendantOf($other);

    public function isAncestorOf($other);

    public function isSelfOrAncestorOf($other);

    public function equals($node);

    public function insideSubtree($node);

    public function inSameScope($node);
}

<?php

namespace Drmovi\PackageBoundaries\Handlers;

use PhpParser\Node;

class ClassConstFetch implements Handler
{

    public function __construct(private readonly Node $node)
    {
    }

    public function can(): bool
    {
        return $this->node instanceof Node\Expr\ClassConstFetch;
    }

    public function getClassNames(): array
    {
        return $this->node->class->toString() ? [$this->node->class->toString()] : [];
    }
}

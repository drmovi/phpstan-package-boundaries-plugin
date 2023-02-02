<?php

namespace Drmovi\PackageBoundaries\Handlers;

use PhpParser\Node;

class UseUse implements Handler
{

    public function __construct(private readonly Node $node)
    {
    }

    public function can(): bool
    {
        return $this->node instanceof Node\Stmt\UseUse;
    }

    public function getClassNames(): array
    {
        return $this->node->name->toString() ? [$this->node->name->toString()] : [];
    }

}

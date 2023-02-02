<?php

namespace Drmovi\PackageBoundaries\Handlers;

use PhpParser\Node;

class FullyQualified implements Handler
{
    public function __construct(private readonly Node $node)
    {
    }

    public function can(): bool
    {
        return $this->node instanceof Node\Name\FullyQualified;
    }

    public function getClassNames(): array
    {
        return $this->node->toString() ? [$this->node->toString()] : [];
    }
}

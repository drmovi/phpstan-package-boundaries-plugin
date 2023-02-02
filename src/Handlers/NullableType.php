<?php

namespace Drmovi\PackageBoundaries\Handlers;

use PhpParser\Node;

class NullableType implements Handler
{

    public function __construct(private readonly Node $node)
    {
    }

    public function can(): bool
    {
        return $this->node instanceof Node\NullableType;
    }

    public function getClassNames(): array
    {
        return $this->node->type->toString() ? [$this->node->type->toString()] : [];
    }
}

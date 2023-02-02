<?php

namespace Drmovi\PackageBoundaries\Handlers;

use PhpParser\Node;

class Instanceof_ implements Handler
{

    public function __construct(private readonly Node $node)
    {
    }

    public function can(): bool
    {
        return $this->node instanceof Node\Expr\Instanceof_;
    }

    public function getClassNames(): array
    {
       return $this->node->class->toString() ? [$this->node->class->toString()] : [];
    }
}

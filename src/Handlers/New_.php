<?php

namespace Drmovi\PackageBoundaries\Handlers;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;

class New_ implements Handler
{

    public function __construct(private readonly Node $node)
    {
    }

    public function can(): bool
    {
        return $this->node instanceof Node\Expr\New_;
    }


    public function getClassNames(): array
    {
        return match (true) {
            $this->node->class instanceof Name => [$this->node->class->toString()],
            $this->node->class instanceof Class_ => [],
            default => [],
        };
    }

}

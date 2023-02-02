<?php

namespace Drmovi\PackageBoundaries\Handlers;

use PhpParser\Node;

class TraitUse implements Handler
{

    public function __construct(private readonly Node $node)
    {
    }

    public function can(): bool
    {
        return $this->node instanceof Node\Stmt\TraitUse;
    }

    public function getClassNames(): array
    {
        $data = [];
        foreach ($this->node->traits as $trait) {
            $data[] = $trait->toString();
        }
        return $data;
    }
}

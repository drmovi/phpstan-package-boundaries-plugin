<?php

namespace Drmovi\PackageBoundaries\Handlers;

use PhpParser\Node;

class UnionType implements Handler
{

    public function __construct(private readonly Node $node)
    {
    }

    public function can(): bool
    {
        return $this->node instanceof Node\UnionType;
    }

    public function getClassNames(): array
    {
       $data  = [];
       foreach ($this->node->types as $type) {
           $data[] = $type->toString();
       }
       return $data;
    }
}

<?php

namespace Drmovi\PackageBoundaries\Handlers;

use PhpParser\Node;

interface Handler
{
    public function __construct(Node $node);

    public function can(): bool;

    public function getClassNames(): array;

}

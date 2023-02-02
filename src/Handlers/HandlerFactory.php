<?php

namespace Drmovi\PackageBoundaries\Handlers;

use PhpParser\Node;
use PHPStan\Analyser\Scope;

class HandlerFactory
{

    private array $handlers = [
        FullyQualified::class,
        UseUse::class,
        New_::class,
        ClassConstFetch::class,
        TraitUse::class,
        UnionType::class,
        NullableType::class,
        Instanceof_::class
    ];


    public function create(Node $node, Scope $scope): ?Handler
    {
        foreach ($this->handlers as $handler) {
            $handler = new $handler($node,$scope);
            if ($handler->can()) {
                return $handler;
            }
        }
        return null;
    }
}

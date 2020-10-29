<?php
namespace Pluf\Scion\Process;

use Pluf\Scion\UnitTracker;

class UnitChain
{

    private UnitTracker $parent;

    public function __construct(UnitTracker $parent)
    {
        $this->parent = $parent;
    }

    public function __invoke($container)
    {
        $this->parent->setLastContainer($container);
        return $this->parent->next();
    }
}


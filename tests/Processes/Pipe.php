<?php
namespace Pluf\Tests\Processes;

use Pluf\Scion\UnitTracker;

class Pipe
{

    public function __invoke(UnitTracker $unitTracker)
    {
        return $unitTracker->next();
    }
}


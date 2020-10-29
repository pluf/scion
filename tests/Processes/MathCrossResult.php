<?php
namespace Pluf\Tests\Processes;

use Pluf\Scion\UnitTracker;

class MathCrossResult
{

    private int $time = 0;

    public function __construct(int $times = 2)
    {
        $this->time = $times;
    }

    public function __invoke(UnitTracker $unitTracker)
    {
        return $this->time * $unitTracker->next();
    }
}


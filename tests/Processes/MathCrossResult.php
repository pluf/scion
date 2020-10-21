<?php
namespace Pluf\Tests\Processes;

use Pluf\Scion\ProcessTracker;

class MathCrossResult
{

    private int $time = 0;

    public function __construct(int $times = 2)
    {
        $this->time = $times;
    }

    public function __invoke(ProcessTracker $processTracker)
    {
        return $this->time * $processTracker->next();
    }
}


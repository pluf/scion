<?php
namespace Pluf\Tests\Processes;

use Pluf\Scion\ProcessTrackerInterface;

class Pipe
{

    public function __invoke(ProcessTrackerInterface $processTracker)
    {
        return $processTracker->next();
    }
}


<?php
namespace Pluf\Tests\Processes;

class MathAddABDeadend
{

    public function __invoke($a, $b = 1)
    {
        return $a + $b;
    }
}


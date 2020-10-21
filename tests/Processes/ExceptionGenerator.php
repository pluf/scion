<?php
namespace Pluf\Tests\Processes;

use RuntimeException;

class ExceptionGenerator
{

    public function __invoke()
    {
        throw new RuntimeException(ExceptionGenerator::class, 1);
    }
}


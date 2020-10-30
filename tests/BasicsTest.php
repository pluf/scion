<?php
namespace Pluf\Tests;

use PHPUnit\Framework\TestCase;
use Pluf\Scion\Process\HttpProcess;

class BasicsTest extends TestCase
{

    /**
     *
     * @test
     */
    public function checkCallabelClassName()
    {
        $this->assertFalse(is_callable(HttpProcess::class));
    }

    /**
     *
     * @test
     */
    public function checkCallabelClassByMethod()
    {
        $this->assertTrue(method_exists(HttpProcess::class, '__invoke'));
    }
}


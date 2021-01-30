<?php
namespace Pluf\Tests;

use PHPUnit\Framework\TestCase;
use Pluf\Scion\Process\Http\IfPathAndMethodIs;

class BasicsTest extends TestCase
{

    /**
     *
     * @test
     */
    public function checkCallabelClassName()
    {
        $this->assertFalse(is_callable(IfPathAndMethodIs::class));
    }

    /**
     *
     * @test
     */
    public function checkCallabelClassByMethod()
    {
        $this->assertTrue(method_exists(IfPathAndMethodIs::class, '__invoke'));
    }
}


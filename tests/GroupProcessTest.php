<?php
namespace Pluf\Tests;

use PHPUnit\Framework\TestCase;
use Pluf\Scion\Process;
use Pluf\Scion\UnitTracker;
use Pluf\Scion\Exceptions\ImposibleToLoadUnits;
use Pluf\Scion\Process\Group;
use Pluf\Tests\Processes\ExceptionGenerator;
use Pluf\Tests\Processes\MathAddABDeadend;
use Pluf\Tests\Processes\MathCrossResult;
use Pluf\Tests\Processes\Pipe;
use InvalidArgumentException;
use RuntimeException;

class GroupProcessTest extends TestCase
{

    /**
     *
     * @test
     */
    public function simpleGroup()
    {
        $ut = new UnitTracker([
            [
                new Process\Group([
                    [
                        new MathCrossResult(2),
                        new MathAddABDeadend()
                    ]
                ])
            ]
        ]);

        $this->assertEquals(4, $ut->doProcess([
            'a' => 1
        ]), 'The sum process not work');
    }

    /**
     *
     * @test
     */
    public function simpleGroupChainTest()
    {
        $ut = new UnitTracker([
            [
                new MathCrossResult(2),
                new Process\Group([
                    [
                        new MathAddABDeadend()
                    ]
                ])
            ]
        ]);

        $this->assertEquals(4, $ut->doProcess([
            'a' => 1
        ]), 'The sum process not work');
    }

    /**
     *
     * @test
     */
    public function simpleGroupChainUnitsTest()
    {
        $ut = new UnitTracker([
            [
                new MathCrossResult(2)
            ],
            [
                new Process\Group([
                    [
                        new MathAddABDeadend()
                    ]
                ])
            ]
        ]);

        $this->assertEquals(4, $ut->doProcess([
            'a' => 1
        ]), 'The sum process not work');
    }

    /**
     *
     * @test
     */
    public function simpleGroupChainUnits2Test()
    {
        $ut = new UnitTracker([
            new Process\Group([
                new MathCrossResult(2)
            ]),
            new MathAddABDeadend()
        ]);

        $this->assertEquals(4, $ut->doProcess([
            'a' => 1
        ]), 'The sum process not work');
    }

    /**
     *
     * @test
     */
    public function simpleGroupChainUnitsException2Test()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(ExceptionGenerator::class);

        $ut = new UnitTracker([
            [
                new Pipe(),
                new Process\Group([
                    new Pipe()
                ])
            ],
            [
                new Pipe(),
                new ExceptionGenerator()
            ]
        ]);
        $ut->doProcess();
    }

    /**
     *
     * @test
     */
    public function simpleFullPipe()
    {
        $ut = new UnitTracker([
            [
                new Pipe(),
                new Process\Group([
                    [
                        new Pipe(),
                        new Pipe()
                    ]
                ])
            ],
            [
                new Pipe()
            ],
            [
                new Pipe()
            ],
            [
                new Pipe()
            ]
        ]);
        $ut->doProcess();
    }

    /**
     *
     * @test
     */
    public function invaliedGroup()
    {
        $this->expectException(ImposibleToLoadUnits::class);
        $ut = new UnitTracker([
            [
                new Process\Group('/a/not/valid/path')
            ]
        ]);
        $ut->doProcess();
    }

    /**
     *
     * @test
     */
    public function inivalidCreateGroup()
    {
        $this->expectException(InvalidArgumentException::class);
        new Group(null);
    }
}
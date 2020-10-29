<?php
namespace Pluf\Tests;

use PHPUnit\Framework\TestCase;
use Pluf\Di\Container;
use Pluf\Scion\UnitTracker;
use Pluf\Scion\Exceptions\UnitTrackerIsBusyException;
use Pluf\Tests\Processes\MathAddABDeadend;
use Pluf\Tests\Processes\MathCrossResult;

class UnitTrackerTest extends TestCase
{

    /**
     * create a process to sum datas
     *
     * @test
     */
    public function simpleSumTest()
    {
        $container = new Container();
        $container['a'] = Container::value(1);
        $container['b'] = Container::value(2);

        $ut = new UnitTracker([
            [
                'condition' => function () {
                    return true;
                },
                function ($a, $b) {
                    return $a + $b;
                }
            ]
        ], $container);

        $result = $ut->doProcess();
        $this->assertEquals(3, $result, 'The sum process not work');
    }

    /**
     * create a process to sum datas
     *
     * @test
     */
    public function simpleSumTestWithInternallContainer()
    {
        $ut = new UnitTracker([ // unit list
            [ // first unit
                'condition' => function () {
                    return true;
                },
                function ($a, $b) { // first process
                    return $a + $b;
                }
            ]
        ]);

        $result = $ut->doProcess([
            'a' => 1,
            'b' => 2
        ]);
        $this->assertEquals(3, $result, 'The sum process not work');
    }

    /**
     * create a process to sum datas
     *
     * @test
     */
    public function simpleSumTestWithInternallContainerNoCondtion()
    {
        $ut = new UnitTracker([ // unit list
            [ // first unit
                function ($a, $b) { // first process
                    return $a + $b;
                }
            ]
        ]);

        $this->assertEquals(3, $ut->doProcess([
            'a' => 1,
            'b' => 2
        ]), 'The sum process not work');
        $this->assertEquals(7, $ut->doProcess([
            'a' => 5,
            'b' => 2
        ]), 'The sum process not work');
    }

    /**
     *
     * @test
     */
    public function supportDefaultValueOfProcess()
    {
        $ut = new UnitTracker([ // unit list
            [ // first unit
                function ($a, int $b = 1) { // first process
                    return $a + $b;
                }
            ]
        ]);

        $this->assertEquals(2, $ut->doProcess([
            'a' => 1
        ]), 'The sum process not work');
        $this->assertEquals(6, $ut->doProcess([
            'a' => 5
        ]), 'The sum process not work');
    }

    /**
     *
     * @test
     */
    public function sendValueInternally()
    {
        $ut = new UnitTracker([ // unit list
            [ // first unit
                function ($unitTracker) {
                    return $unitTracker->next([
                        'a' => 1,
                        'b' => 1
                    ]);
                },
                function ($a, int $b = 1) { // first process
                    return $a + $b;
                }
            ]
        ]);

        $this->assertEquals(2, $ut->doProcess([
            'a' => 1000,
            'b' => 1000
        ]), 'imposible to override services');
    }

    /**
     *
     * @test
     */
    public function multiprocesstest()
    {
        $ut = new UnitTracker([
            [
                function ($unitTracker) {
                    return 2 * $unitTracker->next();
                },
                function ($a, int $b = 1) {
                    return $a + $b;
                }
            ]
        ]);

        $this->assertEquals(4, $ut->doProcess([
            'a' => 1
        ]), 'The sum process not work');
        $this->assertEquals(12, $ut->doProcess([
            'a' => 5
        ]), 'The sum process not work');
    }

    /**
     *
     * @test
     */
    public function multiUnitProcessTest()
    {
        $ut = new UnitTracker([
            [
                function ($unitTracker) {
                    return 2 * $unitTracker->next();
                }
            ],
            [
                function ($a, int $b = 1) {
                    return $a + $b;
                }
            ]
        ]);

        $this->assertEquals(4, $ut->doProcess([
            'a' => 1
        ]), 'The sum process not work');
        $this->assertEquals(12, $ut->doProcess([
            'a' => 5
        ]), 'The sum process not work');
    }

    /**
     *
     * @test
     */
    public function useInvokable()
    {
        $ut = new UnitTracker([
            [
                new MathCrossResult(2),
                new MathAddABDeadend()
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
    public function emptyUnit()
    {
        $ut = new UnitTracker([]);
        $this->assertNull($ut->doProcess());
    }

    /**
     *
     * @test
     */
    public function allFalseCondition()
    {
        $ut = new UnitTracker([
            [
                'condition' => function () {
                    return false;
                },
                function () {
                    throw RuntimeException('Not valid process');
                }
            ],
            [
                function () {
                    return false;
                },
                function () {
                    throw RuntimeException('Not valid process');
                }
            ]
        ]);
        $this->assertFalse($ut->doProcess());
    }

    /**
     *
     * @test
     */
    public function checkCycleUnitTracker()
    {
        $this->expectException(UnitTrackerIsBusyException::class);
        $ut = null;
        $ut = new UnitTracker([
            [
                function () use (&$ut) {
                    return $ut->doProcess();
                }
            ]
        ]);
        $ut->doProcess();
    }

    /**
     *
     * @test
     */
    public function useInvokableAndUnit()
    {
        $ut = new UnitTracker([
            new MathCrossResult(2),
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
    public function useInvokableAndUnitNested()
    {
        $ut = new UnitTracker([
            [
                [
                    [
                        [
                            [
                                new MathCrossResult(2)
                            ]
                        ]
                    ]
                ],
                new MathAddABDeadend()
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
    public function invokeRegisterdProcess()
    {
        $container = new Container();
        // constant
        $container['cross'] = Container::value([
            [
                [
                    [
                        new MathCrossResult(2)
                    ]
                ]
            ]
        ]);
        // factory
        $container['add'] = function () {
            return new MathAddABDeadend();
        };

        $ut = new UnitTracker([
            'cross',
            'add'
        ], $container);

        $this->assertEquals(4, $ut->doProcess([
            'a' => 1
        ]), 'The sum process not work');
    }
}
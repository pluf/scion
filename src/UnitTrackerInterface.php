<?php
namespace Pluf\Scion;

use Pluf\Di\Container;

interface UnitTrackerInterface
{

    /**
     * Performes the next process.
     *
     * @param array $resolves
     */
    public function next(array $resolves = []);

    /**
     * Jumps to the labed process
     *
     * The default label is `end`, which means the end of the unit. In this case
     * the process will jump to the end of unit and none of processes will run.
     *
     * @param array $resolves
     */
    public function jump(array $resolves = [], string $label = 'end');

    /**
     * Changes the latest container
     *
     * All proceding containers will be children of the latest one. This method changes the
     * latest on.
     *
     * @param Container $container
     * @return UnitTrackerInterface
     */
    public function setLastContainer(Container $container): UnitTrackerInterface;
}


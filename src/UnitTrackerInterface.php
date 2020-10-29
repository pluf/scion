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


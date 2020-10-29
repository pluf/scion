<?php
namespace Pluf\Scion\Process;

use Pluf\Di\Container;
use Pluf\Scion\UnitTracker;
use Pluf\Scion\Exceptions\ImposibleToLoadUnits;
use InvalidArgumentException;
use Pluf\Scion\UnitTrackerInterface;

/**
 * Imports list of units and run with a new UnitTracker.
 *
 * @author maso
 *        
 */
class Group
{

    private $rawUnits = null;

    /**
     * Creates new instanse
     */
    public function __construct($rawUnits)
    {
        if (! isset($rawUnits)) {
            throw new InvalidArgumentException('Group process needs array/url of units');
        }
        $this->rawUnits = $rawUnits;
    }

    /**
     * Loads units
     *
     * @return array
     */
    public function loadUnits()
    {
        if (is_array($this->rawUnits)) {
            return $this->rawUnits;
        }
        throw new ImposibleToLoadUnits($this->rawUnits);
    }

    /**
     * Loads units and runs them
     *
     * @param UnitTrackerInterface $unitTracker
     * @param Container $container
     * @return void|mixed
     */
    public function __invoke(UnitTrackerInterface $unitTracker, Container $container)
    {
        $units = array_merge($this->loadUnits(), [
            function (Container $container) use (&$unitTracker) {
                $unitTracker->setLastContainer($container);
                return $unitTracker->next();
            }
        ]);
        $unitTrackerInternall = new UnitTracker($units, $container);
        return $unitTrackerInternall->doProcess();
    }
}


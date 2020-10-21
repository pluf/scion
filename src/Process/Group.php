<?php
namespace Pluf\Scion\Process;

use Pluf\Scion\UnitTracker;
use Pluf\Scion\Exceptions\ImposibleToLoadUnits;
use InvalidArgumentException;
use Pluf\Scion\ProcessTrackerInterface;
use Pluf\Di\Container;

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
     * @param ProcessTrackerInterface $processChain
     * @param Container $container
     * @return void|mixed
     */
    public function __invoke(ProcessTrackerInterface $processTracker, Container $container)
    {
        $lastUnits = [
            [
                function (Container $container) use (&$processTracker) {
                    $processTracker->setLastContainer($container);
                    return $processTracker->next();
                }
            ]
        ];
        $units = array_merge($this->loadUnits(), $lastUnits);
        $unitTracker = new UnitTracker($units, $container);
        return $unitTracker->doProcess();
    }
}


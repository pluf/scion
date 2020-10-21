<?php
namespace Pluf\Scion;

use Pluf\Di\Container;
use Pluf\Di\Invoker;
use Pluf\Di\ParameterResolver\DefaultValueResolver;
use Pluf\Di\ParameterResolver\ResolverChain;
use Pluf\Di\ParameterResolver\Container\ParameterNameContainerResolver;
use Pluf\Scion\Exceptions\UnitTrackerIsBusyException;
use Psr\Container\ContainerInterface;
use ArrayIterator;

/**
 * Default Unit tracker
 *
 * Unit tracker is responsible to find and execute a unit of processes and try
 * to find the next unit.
 *
 * If there is no unit tracke follow the reverse follow.
 *
 * @author Mostafa Barmshory <mostafa.barmshory@gmail.com>
 *        
 */
class UnitTracker extends AbstractTracker
{

    private int $unitPointer = 0;

    private ?ProcessTracker $lastProcessTracker;

    private Container $lastContainer;

    private array $originUnits;

    private ArrayIterator $unitsIterator;

    private bool $nextCalled = false;

    private bool $busy = false;

    /**
     * Creates new instance of UnitTracker
     *
     * @param array $units
     * @param ContainerInterface $container
     */
    function __construct(array $units = [], ContainerInterface $container = null)
    {
        parent::__construct($container);
        $this->originUnits = $units;
        $this->lastContainer = new Container($this->rootContainer);

        $this->loadUnits($units);
    }

    /**
     * Changes list of units
     *
     * @param array $units
     */
    public function loadUnits(array $units = [])
    {
        $this->unitsIterator = new ArrayIterator($units);
        // $this->unitsIterator->rewind();
    }

    protected function findNextUnit()
    {
        $invoker = new Invoker(new ResolverChain([
            new ParameterNameContainerResolver($this->lastContainer),
            new DefaultValueResolver()
        ]));
        while ($this->unitsIterator->valid()) {
            $unit = $this->unitsIterator->current();
            $this->unitsIterator->next();
            $condition = true;
            // condition
            if (array_key_exists('condition', $unit)) {
                $condition = $unit['condition'];
                if (is_callable($condition)) {
                    $condition = $invoker->call($condition);
                }
//             } else if (array_key_exists('regex', $unit)) {
//                 // TODO: maso, 2020: support regex
//                 throw new RuntimeException('REGEX not supported');
            }
            if ($condition) {
                return $unit;
            }
        }
    }

    public function doProcess(array $resolves = [])
    {
        if ($this->busy) {
            throw new UnitTrackerIsBusyException();
        }
        try {
            $this->busy = true;
            return $this->next($resolves);
        } finally{
            $this->busy = false;
        }
    }

    /**
     * Calling the next unit
     */
    public function next(array $resolves = [])
    {
        $this->nextCalled = true;
        // check if ends
        if (! $this->unitsIterator->valid() || ! ($nextUnit = $this->findNextUnit())) {
            // This is the final unit and the tracker is restart
            $this->lastContainer = new Container($this->rootContainer);
            $this->loadUnits($this->originUnits);
            return;
        }

        // create new container
        $this->lastContainer = new Container($this->lastContainer);
        foreach ($resolves as $key => $value) {
            $this->lastContainer[$key] = Container::value($value);
        }

        // call next unit
        $this->lastProcessTracker = new ProcessTracker($nextUnit, $this->lastContainer, $this);
        $this->nextCalled = false;
        $result = $this->lastProcessTracker->next();
        if (! $this->nextCalled) {
            $this->lastContainer = new Container($this->rootContainer);
            $this->loadUnits($this->originUnits);
        }
        return $result;
    }
}


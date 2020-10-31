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
class UnitTracker implements UnitTrackerInterface
{

    /**
     * The root container
     *
     * @var ContainerInterface
     */
    private Container $rootContainer;

    private Container $lastContainer;

    private array $originUnits;

    private ArrayIterator $unitsIterator;

    private bool $busy = false;

    private ?UnitTrackerInterface $parent;

    /**
     * Creates new instance of UnitTracker
     *
     * @param array $units
     * @param ContainerInterface $container
     */
    function __construct(array $units = [], ContainerInterface $container = null, UnitTrackerInterface $parent = null)
    {
        $this->parent = $parent;
        if (! isset($container) || ! ($container instanceof Container)) {
            $container = new Container();
        }
        $this->rootContainer = $container;
        $this->originUnits = $units;
    }

    /**
     * Changes list of units
     *
     * @param array $units
     */
    public function loadUnits(array $units = [])
    {
        $this->unitsIterator = new ArrayIterator($units);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Pluf\Scion\UnitTrackerInterface::setLastContainer()
     */
    public function setLastContainer($container): UnitTrackerInterface
    {
        $this->lastContainer = $container;
        return $this;
    }

    protected function findNextUnit()
    {
        if ($this->unitsIterator->valid()) {
            $unit = $this->unitsIterator->current();
            $this->unitsIterator->next();
            return $unit;
        }
    }

    public function doProcess(array $resolves = [])
    {
        if ($this->busy) {
            throw new UnitTrackerIsBusyException();
        }
        try {
            $this->busy = true;
            $this->lastContainer = new Container($this->rootContainer);
            $this->loadUnits($this->originUnits);
            $resolves['unitTracker'] = $this;
            return $this->next($resolves);
        } finally {
            $this->busy = false;
        }
    }

    /**
     * Calling the next unit
     */
    public function next(array $resolves = [])
    {
        // check if ends
        if (! ($nextUnit = $this->findNextUnit())) {
            // This is the final unit and the tracker is restart
            if (isset($this->parent)) {
                return $this->parent->next($resolves);
            }
            return;
        }

        // create new container
        $this->lastContainer = $container = new Container($this->lastContainer);
        foreach ($resolves as $key => $value) {
            $this->lastContainer[$key] = Container::value($value);
        }

        // call next unit
        if (is_string($nextUnit)) {
            // if is invokable class
            if (class_exists($nextUnit, true) && method_exists($nextUnit, '__invoke')) {
                $nextUnit = new $nextUnit();
            } else {
                // if is a service
                $nextUnit = $container[$nextUnit];
            }
        }
        if (is_callable($nextUnit)) {
            $invoker = new Invoker(new ResolverChain([
                new ParameterNameContainerResolver($container),
                new DefaultValueResolver()
            ]));
            return $invoker->call($nextUnit);
        } else if (is_array($nextUnit)) {
            $unitTracker = new UnitTracker($nextUnit, $container, $this);
            return $unitTracker->doProcess();
        }
        throw Exception('unsupported unit type');
    }

    /**
     *
     * {@inheritdoc}
     * @see \Pluf\Scion\UnitTrackerInterface::jump()
     */
    public function jump(array $resolve = [], string $label = 'end')
    {
        if ($label != 'end') {
            throw new \Exception('Labeled process is not supported.');
        }
        if(!isset($this->parent)){
            // end of main unit
            return;
        }
        return $this->parent->next();
    }
}


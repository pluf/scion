<?php
namespace Pluf\Scion;

use Pluf\Di\Container;
use Pluf\Di\Invoker;
use Pluf\Di\ParameterResolver\DefaultValueResolver;
use Pluf\Di\ParameterResolver\ResolverChain;
use Pluf\Di\ParameterResolver\Container\ParameterNameContainerResolver;
use Psr\Container\ContainerInterface;
use ArrayIterator;

/**
 * A process tracker is responsible to follow the process execution
 *
 * @author mostafa Barmshory (mostafa.barmshory@gmail.com)
 * @since 7
 */
class ProcessTracker extends AbstractTracker implements ProcessTrackerInterface
{

    private ArrayIterator $processIterator;

    private Container $lastContainer;

    private ?UnitTracker $parent = null;

    private bool $nextCalled = false;

    /**
     * Creates new instance of ProcessTracker
     *
     * @param array $units
     * @param ContainerInterface $container
     */
    function __construct(array $unit = [], ContainerInterface $container = null, ?UnitTracker $parent = null)
    {
        parent::__construct($container);
        $this->lastContainer = $this->rootContainer;
        $this->parent = $parent;
        $this->processIterator = new ArrayIterator($unit);
    }

    private function rewind()
    {
        $this->processIterator->rewind();
    }

    /*
     * Must used after #hasMoreProcess
     */
    private function getNextProcess()
    {
        $process = $this->processIterator->current();
        $this->processIterator->next();
        return $process;
    }

    /*
     * Checks if there is a process left
     */
    private function hasMoreProcess()
    {
        while ($this->processIterator->valid()) {
            if ($this->processIterator->key() === 'condetion') {
                $this->processIterator->next();
                continue;
            }
            break;
        }
        return $this->processIterator->valid();
    }

    /*
     * Generates a new container and chine it to the latest one
     */
    private function generateContainer(array $resolves)
    {
        $this->lastContainer = new Container($this->lastContainer);
        foreach ($resolves as $key => $value) {
            $this->lastContainer[$key] = Container::value($value);
        }
        $this->lastContainer['processTracker'] = Container::value($this);
        return $this->lastContainer;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Pluf\Scion\ProcessTrackerInterface::next()
     */
    public function next(array $resolves = [])
    {
        $this->nextCalled = true;
        // check if ends
        if (! $this->hasMoreProcess()) {
            // This is the final unit
            $this->rewind();
            return $this->parent->next();
        }

        // create new container
        $process = $this->getNextProcess();
        $container = $this->generateContainer($resolves);

        // call next unit
        $invoker = new Invoker(new ResolverChain([
            new ParameterNameContainerResolver($container),
            new DefaultValueResolver()
        ]));
        $this->nextCalled = false;
        $result = $invoker->call($process);
        if (! $this->nextCalled) {
            // process return without calling next
            $this->rewind();
        }
        return $result;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Pluf\Scion\ProcessTrackerInterface::setLastContainer()
     */
    public function setLastContainer(Container $container): ProcessTrackerInterface
    {
        $this->lastContainer = $container;
        return $this;
    }
}


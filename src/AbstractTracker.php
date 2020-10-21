<?php
namespace Pluf\Scion;

use Pluf\Di\Container;
use Psr\Container\ContainerInterface;

abstract class AbstractTracker
{

    /**
     * The root container
     *
     * @var ContainerInterface
     */
    protected Container $rootContainer;

    /**
     * Creates new instance of UnitTracker
     *
     * @param array $units
     * @param ContainerInterface $container
     */
    function __construct(ContainerInterface $container = null)
    {
        if (! isset($container) || ! ($container instanceof Container)) {
            $container = new Container($container);
        }
        $this->rootContainer = $container;
    }
}


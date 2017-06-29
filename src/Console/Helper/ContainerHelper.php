<?php

namespace Gmo\Common\Console\Helper;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\Helper;

/**
 * A helper that exposes a PSR-11 container to application/commands.
 */
class ContainerHelper extends Helper
{
    /** @var ContainerInterface */
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'container';
    }
}

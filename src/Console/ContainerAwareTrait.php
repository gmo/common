<?php

namespace Gmo\Common\Console;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;

/**
 * A helper for commands that adds a shortcut method for getting the container from the ContainerHelper.
 *
 * @mixin Command
 */
trait ContainerAwareTrait
{
    /**
     * Get a service by ID from the container if it exists or create it with the callable.
     *
     * @param string   $id
     * @param callable $creator
     *
     * @return mixed
     */
    protected function getOrCreate(string $id, callable $creator)
    {
        if (!$this->getHelperSet()->has('container')) {
            return $creator();
        }

        $container = $this->getContainer();
        if ($container->has($id)) {
            return $container->get($id);
        }

        return $creator();
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->getHelper('container');
    }
}

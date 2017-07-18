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
    protected function getContainer(): ContainerInterface
    {
        return $this->getHelper('container');
    }
}

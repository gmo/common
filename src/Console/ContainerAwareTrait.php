<?php

namespace Gmo\Common\Console;

use Gmo\Common\Console\Helper\ContainerHelper;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;

/**
 * A helper for commands that adds a shortcut method for getting the container from the ContainerHelper.
 *
 * @mixin Command
 */
trait ContainerAwareTrait
{
    public function getContainer(): ContainerInterface
    {
        /** @var ContainerHelper $helper */
        $helper = $this->getHelperSet()->get('container');

        return $helper->getContainer();
    }
}

<?php

namespace Gmo\Common\Dependency;

use Bolt\Collection\Bag;
use Gmo\Common\Exception\Dependency\UnknownDependencyException;
use IteratorAggregate;

interface DependencyInterface extends IteratorAggregate
{
    /**
     * Return the ID for the key or item.
     *
     * @param mixed $keyOrItem
     *
     * @return mixed
     */
    public function getId($keyOrItem);

    /**
     * Return the immediate dependencies for the key or item given.
     * This should not include parent dependencies.
     *
     * @param mixed $keyOrItem
     *
     * @return mixed[]
     */
    public function getDependencies($keyOrItem);

    /**
     * Map the sorted keys to the data set and return it.
     *
     * @param Bag $sortedKeys
     *
     * @return Bag
     */
    public function getSorted(Bag $sortedKeys);

    /**
     * Verify the given dependency exists within the data set.
     *
     * @param mixed $keyOrItem
     *
     * @throws UnknownDependencyException
     */
    public function verify($keyOrItem);
}

<?php

namespace Gmo\Common\Dependency;

/**
 * A MapDependency that implements {@see getDependencies} as a callback given via constructor.
 */
class CallbackMapDependency extends AbstractMapDependency
{
    /** @var callable */
    private $getDependencies;

    /**
     * Constructor.
     *
     * @param iterable $map
     * @param callable $getDependencies Function is called with (item, key)
     */
    public function __construct($map, callable $getDependencies)
    {
        parent::__construct($map);
        $this->getDependencies = $getDependencies;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies($key)
    {
        return call_user_func($this->getDependencies, $this->data[$key], $key);
    }
}

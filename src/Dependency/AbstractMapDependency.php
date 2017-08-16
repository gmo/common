<?php

namespace Gmo\Common\Dependency;

use Bolt\Collection\Bag;
use Gmo\Common\Exception\Dependency\UnknownDependencyException;
use Webmozart\Assert\Assert;

/**
 * Base functionality for a map of objects.
 *
 * The keys in the map should identify items.
 *
 * {@see getDependencies} should return a list of strings which correspond to the keys in the data set.
 */
abstract class AbstractMapDependency implements DependencyInterface
{
    /** @var Bag */
    protected $data;

    /**
     * Constructor.
     *
     * @param iterable $map
     */
    public function __construct($map)
    {
        Assert::isTraversable($map);

        $this->data = Bag::from($map);
    }

    /**
     * {@inheritdoc}
     */
    public function getId($key)
    {
        return $key;
    }

    /**
     * {@inheritdoc}
     */
    public function verify($key)
    {
        if (!isset($this->data[$key])) {
            throw new UnknownDependencyException($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->data->keys();
    }

    /**
     * {@inheritdoc}
     */
    public function getSorted(Bag $sortedKeys)
    {
        return $sortedKeys->flip()->map(function ($key) {
            return $this->data[$key];
        });
    }
}

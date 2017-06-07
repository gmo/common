<?php

namespace Gmo\Common\Dependency;

use Bolt\Collection\ImmutableBag;

/**
 * Base functionality for lists of objects that are identified by a property of the object.
 */
abstract class AbstractListDependency extends AbstractMapDependency
{
    /**
     * Constructor.
     *
     * @param iterable $list
     */
    public function __construct($list)
    {
        $map = [];
        foreach ($list as $item) {
            $map[$this->getId($item)] = $item;
        }

        parent::__construct($map);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getSorted(ImmutableBag $sortedKeys)
    {
        return parent::getSorted($sortedKeys)->values();
    }

    /**
     * {@inheritdoc}
     */
    public function verify($item)
    {
        parent::verify($this->getId($item));
    }
}

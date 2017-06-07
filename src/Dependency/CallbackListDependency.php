<?php

namespace Gmo\Common\Dependency;

/**
 * A ListDependency that implements {@see getId} and {@see getDependencies} as callbacks given via constructor.
 */
class CallbackListDependency extends AbstractListDependency
{
    /** @var callable */
    private $getId;
    /** @var callable */
    private $getDependencies;

    /**
     * Constructor.
     *
     * @param iterable $list
     * @param callable $getId Function is called with (item)
     * @param callable $getDependencies Function is called with (item)
     */
    public function __construct($list, callable $getId, callable $getDependencies)
    {
        $this->getId = $getId;
        $this->getDependencies = $getDependencies;
        parent::__construct($list);
    }

    /**
     * {@inheritdoc}
     */
    public function getId($item)
    {
        return call_user_func($this->getId, $item);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies($item)
    {
        return call_user_func($this->getDependencies, $item);
    }
}

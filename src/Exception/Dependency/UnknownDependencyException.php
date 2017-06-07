<?php

namespace Gmo\Common\Exception\Dependency;

class UnknownDependencyException extends \RuntimeException implements DependencyException
{
    /** @var string */
    private $item;
    /** @var string */
    private $dependency;
    /** @var string */
    private $rawMessage;

    /**
     * Constructor.
     *
     * @param string          $dependency
     * @param string          $item
     * @param string          $message
     * @param \Throwable|null $previous
     */
    public function __construct(
        $dependency,
        $item = 'unknown',
        $message = "Dependency '%dependency%' from item '%item%' does not exist within data set.",
        $previous = null
    ) {
        $this->rawMessage = $message;
        $this->dependency = $dependency;
        $this->setItem($item);
        $this->setDependency($dependency);
        parent::__construct($this->message, 0, $previous);
    }

    /**
     * @return string
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param string $item
     */
    public function setItem($item)
    {
        $this->item = $item;
        $this->updateRepr();
    }

    /**
     * @return string
     */
    public function getDependency()
    {
        return $this->dependency;
    }

    /**
     * @param string $dependency
     */
    public function setDependency($dependency)
    {
        $this->dependency = $dependency;
        $this->updateRepr();
    }

    /**
     * Updates the message representation with the item and dependency.
     */
    private function updateRepr()
    {
        $this->message = $this->rawMessage;
        $this->message = strtr($this->message, [
            '%item%'       => $this->item,
            '%dependency%' => $this->dependency,
        ]);
    }
}

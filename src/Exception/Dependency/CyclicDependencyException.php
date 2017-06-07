<?php

namespace Gmo\Common\Exception\Dependency;

class CyclicDependencyException extends \RuntimeException implements DependencyException
{
    /** @var array */
    private $items;
    /** @var string */
    private $rawMessage;
    /** @var string */
    private $itemName = 'items';

    /**
     * Constructor.
     *
     * @param array                      $items
     * @param string                     $message
     * @param string                     $itemName
     * @param \Exception|\Throwable|null $previous
     */
    public function __construct(
        array $items,
        $message = "The %name% %items% have a cyclic dependency.",
        $itemName = 'items',
        $previous = null
    ) {
        $this->items = $items;
        $this->rawMessage = $message;
        $this->setItemName($itemName);
        parent::__construct($this->message, 0, $previous);
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param string $itemName
     */
    public function setItemName($itemName)
    {
        $this->itemName = $itemName;
        $this->updateRepr();
    }

    /**
     * Updates the message representation with the item name and items.
     */
    private function updateRepr()
    {
        $this->message = $this->rawMessage;
        $items = "'" . implode("', '", $this->items) . "'";
        $this->message = strtr($this->message, ['%name%' => $this->itemName, '%items%' => $items]);
    }
}

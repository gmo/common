<?php

namespace Gmo\Common;

use Carbon\Carbon;
use DateTime;
use Gmo\Common\Serialization\SerializableInterface;

/**
 * Out of necessity to maintain ISerializable contract.
 */
class SerializableCarbon extends Carbon implements SerializableInterface
{
    /**
     * {@inheritdoc}
     */
    public static function instance(DateTime $dt)
    {
        if ($dt instanceof self) {
            return clone $dt;
        }

        return new static($dt->format('Y-m-d H:i:s.u'), $dt->getTimezone());
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray($obj)
    {
        return new static($obj['date'], $obj['timezone']);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return ['class' => get_called_class()] + (array) $this;
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $this->__construct();
        $dt = static::fromArray(unserialize($serialized));
        $this->setTimezone($dt->getTimezone());
        $this->setTimestamp($dt->getTimestamp());
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $serialized = serialize((array) $this);

        // Maintain compatibility with parent classes method
        // SerializableCarbon::fromSerialized($carbon->serialize())
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        if (isset($trace[0]['file'])) {
            $serialized = sprintf(
                'C:%d:"%s":%d:{%s}',
                strlen(get_called_class()),
                get_called_class(),
                strlen($serialized),
                $serialized
            );
        }

        return $serialized;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public static function fromJson($json)
    {
        return static::fromArray(Json::parse($json));
    }

    /**
     * {@inheritdoc}
     */
    public function toJson()
    {
        return Json::dump($this->toArray());
    }
}

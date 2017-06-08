<?php

namespace Gmo\Common\Serialization;

use Carbon\Carbon;
use DateTime;

/**
 * Out of necessity to maintain ISerializable contract.
 */
class SerializableCarbon extends Carbon implements SerializableInterface
{
    use SerializableTrait;

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
        return ['class' => static::class] + (array) $this;
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
                strlen(static::class),
                static::class,
                strlen($serialized),
                $serialized
            );
        }

        return $serialized;
    }
}

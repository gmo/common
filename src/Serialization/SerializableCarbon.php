<?php

namespace Gmo\Common\Serialization;

use Carbon\Carbon;
use DateTime;
use Gmo\Common\Deprecated;
use GMO\Common\ISerializable;
use GMO\Common\Json;

/**
 * Out of necessity to maintain ISerializable contract.
 *
 * @deprecated will be removed in 2.0.
 */
class SerializableCarbon extends Carbon implements ISerializable
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
        return array('class' => get_called_class()) + (array) $this;
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $arr = unserialize($serialized);
        $this->__construct($arr['date'], $arr['timezone']);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $serialized = serialize((array) $this);

        // Maintain compatibility with parent classes method
        // SerializableCarbon::fromSerialized($carbon->serialize())
        $trace = PHP_VERSION_ID >= 50400 ? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1) : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
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
        Deprecated::method();

        return static::fromArray(Json::parse($json));
    }

    /**
     * {@inheritdoc}
     */
    public function toJson()
    {
        Deprecated::method();

        return Json::dump($this->toArray());
    }
}
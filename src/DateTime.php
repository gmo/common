<?php

namespace GMO\Common;

Deprecated::cls('GMO\Common\DateTime', 1.0, 'Carbon\Carbon');

/**
 * @deprecated Use {@see Carbon\Carbon Carbon} instead
 */
class DateTime extends \DateTime implements ISerializable
{
    const SIMPLE_DATE = "Y-m-d H:i:s";

    public static function castFromBuiltin(\DateTime $dt)
    {
        $newCls = new static();
        $newCls->setTimestamp($dt->getTimestamp());
        $newCls->setTimezone($dt->getTimezone());

        return $newCls;
    }

    public static function now($timezone = null)
    {
        if ($timezone) {
            return new static(null, $timezone);
        }

        return new static();
    }

    public function toString($format = self::SIMPLE_DATE)
    {
        return $this->format($format);
    }

    public function __toString()
    {
        return $this->toString();
    }

    //region Serializable Methods

    public function toArray()
    {
        return SerializeHelper::serializeObject(get_called_class(), get_object_vars($this));
    }

    public static function fromArray($obj)
    {
        $tz = $obj['timezone'] ? new \DateTimeZone($obj['timezone']) : null;

        return new static($obj['date'], $tz);
    }

    public function toJson()
    {
        return Json::dump($this->toArray());
    }

    public static function fromJson($json)
    {
        return static::fromArray(Json::parse($json));
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function serialize()
    {
        return serialize($this->toArray());
    }

    public function unserialize($serialized)
    {
        $this->__construct();
        $cls = static::fromArray(unserialize($serialized));
        $this->setTimestamp($cls->getTimestamp());
        $this->setTimezone($cls->getTimezone());
    }

    //endregion
}

<?php

namespace Gmo\Common\Serialization;

use Gmo\Common\Deprecated;
use GMO\Common\Exception\NotSerializableException;
use GMO\Common\ISerializable;
use GMO\Common\Json;

/**
 * @deprecated will be removed in 2.0.
 */
abstract class AbstractSerializable implements ISerializable
{
    /**
     * If overriding this, be sure to include "class" with the fully qualified class name
     *
     * @return array
     */
    public function toArray()
    {
        return SerializeHelper::serializeObject(get_called_class(), get_object_vars($this));
    }

    /**
     * Recreates object by calling the constructor with parameters
     * that match the array keys (recursively for objects)
     *
     * Requirements:
     *
     * 1) Constructor parameters that are objects need to be type hinted
     *
     * 2) Constructor parameters that are objects need to implement {@see ISerializable}
     *
     * 3) Constructor parameter names need to match the class variable names
     *
     * @param array $obj
     *
     * @throws NotSerializableException If a constructor takes an object that
     *                                  does not implement {@see ISerializable}
     * @return $this
     */
    public static function fromArray($obj)
    {
        return SerializeHelper::createClassFromArray(get_called_class(), $obj);
    }

    public function toJson()
    {
        Deprecated::method();

        return Json::dump($this->toArray());
    }

    /**
     * @param $json
     *
     * @return $this
     */
    public static function fromJson($json)
    {
        Deprecated::method();

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
        $cls = static::fromArray(unserialize($serialized));
        $properties = get_class_vars(get_called_class());
        foreach ($properties as $property => $value) {
            $this->$property = $cls->$property;
        }
    }
}

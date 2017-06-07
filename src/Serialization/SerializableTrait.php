<?php

namespace Gmo\Common\Serialization;

use GMO\Common\Exception\NotSerializableException;
use GMO\Common\Json;

trait SerializableTrait
{
    /**
     * If overriding this, be sure to include "class" with the fully qualified class name
     *
     * @return array
     */
    public function toArray()
    {
        $values = array(
            "class" => get_called_class(),
        );

        foreach (get_object_vars($this) as $key => $value) {
            if ($value instanceof SerializableInterface) {
                $values[$key] = $value->toArray();
            } elseif ($value instanceof \DateTime) {
                $values[$key] = SerializableCarbon::instance($value)->toArray();
            } elseif ($value instanceof \Exception) {
                $values[$key] = serialize($value);
            } else {
                $values[$key] = $value;
            }
        }

        return $values;
    }

    /**
     * Recreates object by calling the constructor with parameters
     * that match the array keys (recursively for objects)
     *
     * Requirements:
     *
     * 1) Constructor parameters that are objects need to be type hinted
     *
     * 2) Constructor parameters that are objects need to implement {@see SerializableInterface}
     *
     * 3) Constructor parameter names need to match the class variable names
     *
     * @param array $obj
     *
     * @throws NotSerializableException If a constructor takes an object that
     *                                  does not implement {@see SerializableInterface}
     * @return static
     */
    public static function fromArray($obj)
    {
        $cls = new \ReflectionClass(get_called_class());
        $refParams = $cls->getConstructor()->getParameters();
        $params = array();
        foreach ($refParams as $refParam) {
            try {
                $paramCls = $refParam->getClass();
            } catch (\ReflectionException $e) {
                throw new NotSerializableException(
                    sprintf(
                        'The constructor parameter "%s" of class "%s" is type hinting a nonexistent class',
                        $refParam->getName(),
                        get_called_class()
                    )
                );
            }
            if (!array_key_exists($refParam->name, $obj)) {
                $params[] = $refParam->isOptional() ? $refParam->getDefaultValue() : null;
                continue;
            }
            if (!$paramCls) {
                $params[] = $obj[$refParam->name];
            } elseif ($paramCls->isSubclassOf('Gmo\Common\Serialization\SerializableInterface')) {
                /** @var SerializableInterface|string $clsName */
                $clsName = $paramCls->name;
                if (!class_exists($clsName)) {
                    throw new NotSerializableException($clsName . ' does not exist');
                }
                $params[] = $clsName::fromArray($obj[$refParam->name]);
            } elseif ($paramCls->isSubclassOf('\Exception') || $paramCls->getName() === 'Exception') {
                $params[] = unserialize($obj[$refParam->name]);
            } elseif (is_a($paramCls->name, 'DateTime', true)) {
                $params[] = SerializableCarbon::fromArray($obj[$refParam->name]);
            } else {
                throw new NotSerializableException($paramCls->name . ' does not implement Gmo\Common\Serialization\SerializableInterface');
            }
        }

        return $cls->newInstanceArgs($params);
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
        return $this->toJson();
    }

    public function unserialize($serialized)
    {
        $cls = $this->fromJson($serialized);
        $properties = get_class_vars(get_called_class());
        foreach ($properties as $property => $value) {
            $this->$property = $cls->$property;
        }
    }
}

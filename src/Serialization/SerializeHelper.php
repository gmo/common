<?php

namespace Gmo\Common\Serialization;

use GMO\Common\Exception\NotSerializableException;

/**
 * @deprecated will be removed in 2.0.
 */
class SerializeHelper
{
    /**
     * This loops through a given an object's variables and
     * serializes {@see ISerializable} and {@see \DateTime} objects
     *
     * Example:
     * <pre>
     * public function toArray() {
     *     return SerializeHelper::serializeObject(get_called_class(), get_object_vars($this));
     * }
     * </pre>
     *
     * @param $className
     * @param $objVars
     *
     * @return array
     */
    public static function serializeObject($className, $objVars)
    {
        $values = array(
            "class" => $className,
        );

        foreach ($objVars as $key => $value) {
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
     * Example:
     * <pre>
     * public static function fromArray($obj) {
     *     return SerializeHelper::createClassFromArray(get_called_class(), $obj);
     * }
     * </pre>
     *
     * Requirements:
     *
     * 1) Constructor parameters that are objects need to be type hinted
     *
     * 2) Constructor parameters that are objects need to implement {@see SerializableInterface}
     *
     * 3) Constructor parameter names need to match the class variable names
     *
     * @param string $className
     * @param array  $obj
     *
     * @throws NotSerializableException If a constructor takes an object that
     *                                  does not implement {@see SerializableInterface}
     *                                  or if the class does not exist
     * @return $this
     */
    public static function createClassFromArray($className, $obj)
    {
        if (!class_exists($className)) {
            throw new NotSerializableException($className . ' does not exist');
        }
        $cls = new \ReflectionClass($className);
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
                        $className
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

    private function __construct()
    {
    }
}

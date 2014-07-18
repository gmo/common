<?php
namespace GMO\Common;

use GMO\Common\Exception\NotSerializableException;

/**
 * This class was refactored from {@see AbstractSerializable} to make it easier when you
 * need to implement {@see ISerializable} without extending {@see AbstractSerializable}
 * @package GMO\Common
 * @since 1.14.0
 */
class SerializeHelper {

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
	 * @return array
	 */
	public static function serializeObject($className, $objVars) {
		$values = array(
			"class" => $className,
		);

		foreach ($objVars as $key => $value) {
			if ($value instanceof ISerializable) {
				$values[$key] = $value->toArray();
			} elseif ($value instanceof \DateTime) {
				$values[$key] = json_decode(json_encode($value), true);
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
	 * 2) Constructor parameters that are objects need to implement {@see ISerializable}
	 *
	 * 3) Constructor parameter names need to match the class variable names
	 *
	 * @param string $className
	 * @param array $obj
	 * @throws NotSerializableException If a constructor takes an object that
	 *                                  does not implement {@see ISerializable}
	 * @return mixed
	 */
	public static function createClassFromArray($className, $obj) {
		$cls = new \ReflectionClass($className);
		$refParams = $cls->getConstructor()->getParameters();
		$params = array();
		foreach ($refParams as $refParam) {
			$paramCls = $refParam->getClass();
			if (!array_key_exists($refParam->name, $obj)) {
				$params[] = $refParam->isOptional() ? $refParam->getDefaultValue() : null;
				continue;
			}
			if (!$paramCls) {
				$params[] = $obj[$refParam->name];
			} elseif ($paramCls->name === "DateTime") {
				$timestamp = $obj[$refParam->name];
				$tz = $timestamp['timezone'] ? new \DateTimeZone($timestamp['timezone']) : null;
				$params[] = new \DateTime($timestamp['date'], $tz);
			} elseif ($paramCls->isSubclassOf('GMO\Common\ISerializable')) {
				/** @var ISerializable $clsName */
				$clsName = $paramCls->name;
				$params[] = $clsName::fromArray($obj[$refParam->name]);
			} else {
				throw new NotSerializableException($paramCls->name . ' does not implement GMO\Common\ISerializable');
			}
		}
		return $cls->newInstanceArgs($params);
	}

	private function __construct() { }
}

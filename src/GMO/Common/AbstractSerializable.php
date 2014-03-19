<?php
namespace GMO\Common;

use GMO\Common\Exception\NotSerializableException;

abstract class AbstractSerializable implements ISerializable {

	/**
	 * If overriding this, be sure to include "class" with the fully qualified class name
	 * @return array
	 */
	public function toArray() {
		$values = array(
			"class" => get_called_class()
		);

		$objVars = get_object_vars( $this );
		foreach ( $objVars as $key => $value ) {
			if ( $value instanceof ISerializable ) {
				$values[$key] = $value->toArray();
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
	 * 1) Constructor parameters that are objects need to be type hinted
	 * 2) Constructor parameters that are objects need to extend AbstractSerializable or override fromArray
	 * 3) Constructor parameter names need to match the class variable names
	 *
	 * @param array $obj
	 * @throws NotSerializableException if a constructor takes an object that doesn't implement GMO\Common\ISerializable
	 * @return mixed
	 */
	public static function fromArray($obj) {
		$cls = new \ReflectionClass(get_called_class());
		$refParams = $cls->getConstructor()->getParameters();
		$params = array();
		foreach($refParams as $refParam) {
			$paramCls = $refParam->getClass();
			if (!array_key_exists($refParam->name, $obj)) {
				$params[] = $refParam->isOptional() ? $refParam->getDefaultValue() : null;
				continue;
			}
			if (!$paramCls) {
				$params[] = $obj[$refParam->name];
			} elseif ($paramCls->name === "DateTime") {
				$timestamp = $obj[$refParam->name];
				$tz = new \DateTimeZone($timestamp['timezone']);
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

	public function toJson() {
		return json_encode( $this->toArray() );
	}

	public static function fromJson($json) {
		return static::fromArray(json_decode( $json, true ));
	}

	public function jsonSerialize() {
		return $this->toArray();
	}

}

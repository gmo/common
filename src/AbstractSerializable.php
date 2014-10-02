<?php
namespace GMO\Common;

use GMO\Common\Exception\NotSerializableException;

abstract class AbstractSerializable implements ISerializable {

	/**
	 * If overriding this, be sure to include "class" with the fully qualified class name
	 * @return array
	 */
	public function toArray() {
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
	 * @throws NotSerializableException If a constructor takes an object that
	 *                                  does not implement {@see ISerializable}
	 * @return $this
	 */
	public static function fromArray($obj) {
		return SerializeHelper::createClassFromArray(get_called_class(), $obj);
	}

	public function toJson() {
		return json_encode( $this->toArray() );
	}

	/**
	 * @param $json
	 * @return $this
	 */
	public static function fromJson($json) {
		return static::fromArray(json_decode( $json, true ));
	}

	public function jsonSerialize() {
		return $this->toArray();
	}

	public function serialize() {
		return $this->toJson();
	}

	public function unserialize($serialized) {
		$cls = $this->fromJson($serialized);
		$properties = get_class_vars(get_called_class());
		foreach ($properties as $property => $value) {
			$this->$property = $cls->$property;
		}
	}
}

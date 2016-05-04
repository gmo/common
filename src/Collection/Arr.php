<?php

namespace GMO\Common\Collection;

class Arr {

	/**
	 * Returns a key's value from a collection or default value if key does not exist
	 * @param array|\ArrayAccess $collection
	 * @param string|int         $key
	 * @param mixed|null         $default
	 * @return mixed
	 */
	public static function get($collection, $key, $default = null) {
		if (!is_array($collection) && !$collection instanceof \ArrayAccess) {
			throw new \InvalidArgumentException('Collection parameter must be an array or a object implementing ArrayAccess');
		}
		return isset($collection[$key]) ? $collection[$key] : $default;
	}

	/**
	 * Returns whether a key exists in an array
	 * @param array $collection
	 * @param string $key
	 * @return bool
	 */
	public static function containsKey(array $collection, $key) {
		return array_key_exists($key, $collection);
	}

	/**
	 * Returns whether an array contains a value
	 * @param array $collection
	 * @param mixed $value
	 * @return bool
	 */
	public static function containsValue(array $collection, $value) {
		return in_array($value, $collection, true);
	}

	/**
	 * Returns an array with the key's value incremented.
	 * If key does not exist, the key is set with a value of 1.
	 * @param array      $collection
	 * @param string|int $key
	 * @return array
	 */
	public static function increment(array $collection, $key) {
		if (static::containsKey($collection, $key)) {
			$collection[$key]++;
		} else {
			$collection[$key] = 1;
		}
		return $collection;
	}

	/**
	 * Returns whether an array is associative
	 * @param array $collection
	 * @return bool
	 */
	public static function isAssociative(array $collection) {
		return (bool)count(array_filter(array_keys($collection), 'is_string'));
	}

	/**
	 * Returns an array with the value prepended
	 * @param array $list
	 * @param mixed $key
	 * @return array
	 */
	public static function prepend(array $list, $key) {
		array_unshift($list, $key);
		return $list;
	}

	/**
	 * Returns an array with the value appended
	 * @param array $list
	 * @param mixed $key
	 * @return array
	 */
	public static function append(array $list, $key) {
		array_push($list, $key);
		return $list;
	}

	/**
	 * Returns an array with the key/value pair removed
	 * @param array  $collection
	 * @param string $key
	 * @return array
	 */
	public static function remove(array $collection, $key) {
		unset($collection[$key]);
		if (!static::isAssociative($collection)) {
			return array_values($collection);
		}
		return $collection;
	}

	/**
	 * Returns the first value in a list
	 * @param array $list
	 * @return mixed
	 */
	public static function getFirst(array $list) {
		return reset($list);
	}

	/**
	 * Returns the last value in a list
	 * @param array $list
	 * @return mixed
	 */
	public static function getLast(array $list) {
		return end($list);
	}

	/**
	 * Returns the list minus the first element
	 * @param array $list
	 * @return array
	 */
	public static function getTail(array $list) {
		array_shift($list);
		return $list;
	}

	/**
	 * Returns the list minus the last element
	 * @param array $list
	 * @return array
	 */
	public static function getAllButLast(array $list) {
		array_pop($list);
		return $list;
	}

	/**
	 * Merges N number of arrays. Parameters that are not arrays will be converted
	 * @param array $array1
	 * @param array $array2
	 * @param null  $_
	 * @return array
	 */
	public static function merge($array1, $array2, $_ = null) {
		$args = array_map(function($arg) {
			return is_array($arg) ? $arg : array($arg);
		}, func_get_args());
		return call_user_func_array('array_merge', $args);
	}

	/**
	 * Flattens nested collections into a single collection
	 * containing all of the values, and optionally keys
	 * @param array|\Traversable $collection The nested collection to flatten
	 * @param bool               $keys       Whether to use keys as index.
	 * @return array
	 */
	public static function flatten($collection, $keys = true) {
		if (is_array($collection)) {
			$it = new \RecursiveArrayIterator($collection);
		} elseif ($collection instanceof \Traversable) {
			$it = $collection;
		} else {
			throw new \InvalidArgumentException('Collection parameter must be an array or a Traversable object');
		}
		return iterator_to_array(new \RecursiveIteratorIterator($it), $keys);
	}

	/**
	 * Shortcut for get() and remove().
	 * Note: This mutates the collection.
	 * @param array      $collection
	 * @param string     $key
	 * @param mixed|null $default
	 * @return mixed|null
	 */
	public static function pop(array &$collection, $key, $default = null) {
		$val = static::get($collection, $key, $default);
		$collection = static::remove($collection, $key);
		return $val;
	}

	/**
	 * Removes and returns the first element in a list
	 * Note: This mutates the list.
	 * @param array $list
	 * @return mixed|null
	 */
	public static function popFirst(array &$list) {
		return array_shift($list);
	}

	/**
	 * Removes and returns the last element in a list
	 * Note: This mutates the list.
	 * @param array $list
	 * @return mixed|null
	 */
	public static function popLast(array &$list) {
		return array_pop($list);
	}

	/**
	 * Recursively convert stdClass to arrays
	 * @link http://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/
	 * @param $object
	 * @return array
	 */
	public static function objectToArray($object) {
		if (is_object($object)) {
			// Convert stdClass to array
			$object = get_object_vars($object);
		}

		if (is_array($object)) {
			// recursive call for nested conversion
			return array_map(array('static', __FUNCTION__), $object);
		} else {
			return $object;
		}
	}

	public static function isTraversable($object) {
		return is_array($object) || $object instanceof \Traversable;
	}

	private function __construct() { }
}

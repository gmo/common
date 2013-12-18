<?php
namespace GMO\Common;

/**
 * Class Collection
 * @package GMO\Common
 * @since 1.7.0
 */
class Collection {

	/**
	 * Returns a key's value from array or default value if key does not exist
	 * @param array      $collection
	 * @param string|int $key
	 * @param mixed|null $default
	 * @return mixed
	 */
	public static function get(array $collection, $key, $default = null) {
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
	 * Merges two arrays.
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	public static function merge(array $array1, array $array2) {
		return array_merge($array1, $array2);
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
		static::remove($collection, $key);
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

}
<?php

namespace Gmo\Common\Collections;

use ArrayAccess;
use ArrayIterator;
use stdClass;
use Traversable;

/**
 * An Collection wraps a regular PHP array.
 *
 * Generally, methods that modify a single item of the collection return the same collection
 * and those that _can_ modify multiple items return a new collection.
 */
class Collection implements ListInterface, HashInterface
{
	/** @var array An array containing the entries of this collection. */
	protected $items;

	/**
	 * Initializes a new Collection.
	 *
	 * @param Collection|Traversable|array|mixed|null $items
	 */
	public function __construct($items = array())
	{
		$this->items = static::normalizeConstructorAgs(func_get_args());
	}

	/**
	 * Initializes a new Collection.
	 *
	 * @param Collection|Traversable|array|mixed|null $items
	 *
	 * @return static|Collection
	 */
	public static function create($items = array())
	{
		return new static(static::normalizeConstructorAgs(func_get_args()));
	}

	/**
	 * Initializes a new Collection and recursive converts arrays to collections
	 *
	 * @param Collection|Traversable|array|mixed|null $items
	 *
	 * @return static|Collection
	 */
	public static function createRecursive($items = array())
	{
		return static::convertToCollection(static::normalizeConstructorAgs(func_get_args()));
	}

	public function toArray()
	{
		return $this->items;
	}

	public function toArrayRecursive() {
		return static::convertToArray($this->items);
	}

	/**
	 * Returns the first item in the collection.
	 *
	 * @return mixed
	 */
	public function first()
	{
		return reset($this->items);
	}

	/**
	 * Returns the last item in the collection.
	 *
	 * @return mixed
	 */
	public function last()
	{
		return end($this->items);
	}

	/**
	 * Removes the item at the specified index from the collection.
	 *
	 * @param string|integer $key The kex/index of the item to remove.
	 *
	 * @return mixed The removed item or NULL, if the collection did not contain the item.
	 */
	public function remove($key)
	{
		if (isset($this->items[$key]) || array_key_exists($key, $this->items)) {
			$removed = $this->items[$key];
			unset($this->items[$key]);

			return $removed;
		}

		return null;
	}

	/**
	 * Removes and returns the specified item from the collection, if it is found.
	 *
	 * @param mixed $item The item to remove.
	 *
	 * @return mixed The removed item or NULL, if the collection did not contain the item.
	 */
	public function removeItem($item)
	{
		$key = array_search($item, $this->items, true);

		if ($key !== false) {
			$value = $this->items[$key];
			unset($this->items[$key]);
			return $value;
		}

		return null;
	}

	public function removeFirst()
	{
		return $this->removeItem($this->first());
	}

	public function removeLast()
	{
		return $this->removeItem($this->last());
	}

	/**
	 * Checks whether the collection contains an item with the specified key/index.
	 *
	 * @param string|integer $key The key/index to check for.
	 *
	 * @return boolean TRUE if the collection contains an item with the specified key/index,
	 *                 FALSE otherwise.
	 */
	public function has($key)
	{
		return isset($this->items[$key]) || array_key_exists($key, $this->items);
	}

	/**
	 * Checks whether an item is contained in the collection.
	 * This is an O(n) operation, where n is the size of the collection.
	 *
	 * @param mixed $item The item to search for.
	 *
	 * @return boolean TRUE if the collection contains the item, FALSE otherwise.
	 */
	public function hasItem($item)
	{
		return in_array($item, $this->items, true);
	}

	/**
	 * Tests for the existence of an item that satisfies the given predicate.
	 *
	 * @param callable $p The predicate. Function is passed key, value.
	 *
	 * @return boolean TRUE if the predicate is TRUE for at least one item, FALSE otherwise.
	 */
	public function exists($p)
	{
		foreach ($this->items as $key => $item) {
			if ($p($key, $item)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Gets the index/key of a given item. The comparison of two items is strict,
	 * that means not only the value but also the type must match.
	 * For objects this means reference equality.
	 *
	 * @param mixed $item The item to search for.
	 *
	 * @return int|string|bool The key/index of the item or FALSE if the item was not found.
	 */
	public function indexOf($item)
	{
		return array_search($item, $this->items, true);
	}

	/**
	 * Gets the item at the specified key/index.
	 *
	 * @param string|integer $key     The key/index of the item to retrieve.
	 * @param mixed|null     $default The default value to return if not found
	 *
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		if (isset($this->items[$key])) {
			return $this->items[$key];
		}
		return $default;
	}

	/**
	 * Gets all keys/indices of the collection.
	 *
	 * @return static The keys/indices of the collection, in the order of the corresponding
	 *                items in the collection.
	 */
	public function keys()
	{
		return static::create(array_keys($this->items));
	}

	/**
	 * Gets all values of the collection.
	 *
	 * @return static The values of all items in the collection, in the order they
	 *                appear in the collection.
	 */
	public function values()
	{
		return static::create(array_values($this->items));
	}

	public function count()
	{
		return count($this->items);
	}

	/**
	 * Sets an item in the collection at the specified key/index.
	 *
	 * @param string|integer $key   The key/index of the item to set.
	 * @param mixed          $value The item to set.
	 *
	 * @return $this|Collection
	 */
	public function set($key, $value)
	{
		$this->items[$key] = $value;
	}

	/**
	 * Adds an item at the end of the collection.
	 *
	 * @param mixed $item The item to add.
	 *
	 * @return $this|Collection
	 */
	public function add($item)
	{
		$this->items[] = $item;
		return $this;
	}

	/**
	 * @inheritdoc
	 * @return $this|Collection
	 */
	public function prepend($value)
	{
		array_unshift($this->items, $value);
		return $this;
	}

	/**
	 * Replaces items in this collection from another collection by comparing keys
	 * @param Collection|Traversable|array $collection The collection from which items will be extracted.
	 * @param Collection|Traversable|array $_          Optional N-number of collections
	 * @return $this|Collection
	 */
	public function replace($collection, $_ = null)
	{
		$args = static::normalizeArgs(func_get_args());
		array_unshift($args, $this->items);
		$this->items = call_user_func_array('array_replace', $args);
		return $this;
	}

	/**
	 * Replaces items in this collection from another collection recursively by comparing keys
	 * @param Collection|Traversable|array $collection The collection from which items will be extracted.
	 * @param Collection|Traversable|array $_          Optional N-number of collections
	 * @return $this|Collection
	 */
	public function replaceRecursive($collection, $_ = null)
	{
		$args = static::normalizeArgs(func_get_args());
		array_unshift($args, $this->items);
		$args = static::convertToArray($args);
		$items = call_user_func_array('array_replace_recursive', $args);
		$this->items = $this->convertToCollection($items)->toArray();
		return $this;
	}

	/**
	 * Merges items from another collection into this collection.
	 *
	 * If the collections have string keys, the value from the input collection will replace the current value.
	 *
	 * If the collections have numeric keys, the input collection will be appended to this collection.
	 *
	 * @param Collection|Traversable|array $collection The collection from which items will be extracted.
	 * @param Collection|Traversable|array $_          Optional N-number of collections
	 * @return $this|Collection
	 */
	public function merge($collection, $_ = null)
	{
		$args = static::normalizeArgs(func_get_args());
		array_unshift($args, $this->items);
		$this->items = call_user_func_array('array_merge', $args);
		return $this;
	}

	/**
	 * Merges items from another collection into this collection recursively.
	 *
	 * If the collections have string keys, the values from the collections with the same key will
	 * merged to a collection for that key.
	 *
	 * If the collections have numeric keys, the input collection will be appended to this collection.
	 *
	 * @param Collection|Traversable|array $collection The collection from which items will be extracted.
	 * @param Collection|Traversable|array $_          Optional N-number of collections
	 * @return $this
	 */
	public function mergeRecursive($collection, $_ = null)
	{
		$args = static::normalizeArgs(func_get_args());
		array_unshift($args, $this->items);
		$args = static::convertToArray($args);
		$items = call_user_func_array('array_merge_recursive', $args);
		$this->items = $this->convertToCollection($items)->toArray();

		return $this;
	}

	/**
	 * Sets default values on this collection
	 *
	 * Basically the opposite of merge/replace.
	 *
	 * @param Collection|Traversable|array $collection The collection from which items will be extracted.
	 * @return $this|Collection
	 */
	public function defaults($collection) {
		$defaults = static::normalize($collection);
		$this->items = array_replace($defaults, $this->items);

		return $this;
	}

	/**
	 * Checks whether the collection is empty (contains no items).
	 *
	 * @return boolean TRUE if the collection is empty, FALSE otherwise.
	 */
	public function isEmpty()
	{
		return !$this->items;
	}

	/**
	 * Applies the given public function to each item in the collection and returns
	 * a new collection with the items returned by the public function.
	 *
	 * @param callable $func Function is passed value.
	 *
	 * @return static
	 */
	public function map($func)
	{
		return static::create(array_map($func, $this->items));
	}

	/**
	 * Returns all the items of this collection that satisfy the predicate p.
	 * The order of the items is preserved.
	 * If no predicate, all items that equal false will be removed.
	 *
	 * @param callable|null $p The predicate used for filtering. Function can be passed value, key, or key/value.
	 *
	 * @return static A collection with the results of the filter operation.
	 */
	public function filter($p)
	{
		if ($p === null) {
			return static::create(array_filter($this->items));
		}
		$func = new \ReflectionFunction($p);
		$params = $func->getParameters();
		if (count($params) === 1 && $params[0]->getName() !== 'key') {
			return static::create(array_filter($this->items, $p));
		}

		$newItems = new static();
		foreach ($this->items as $key => $value) {
			if ($p($key, $value)) {
				$newItems->set($key, $value);
			}
		}
		return $newItems;

	}

	/**
	 * Tests whether the given predicate p holds for all items of this collection.
	 *
	 * @param callable $p The predicate. Function is passed key, value.
	 *
	 * @return boolean TRUE, if the predicate yields TRUE for all items, FALSE otherwise.
	 */
	public function forAll($p)
	{
		foreach ($this->items as $key => $item) {
			if (!$p($key, $item)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Partitions this collection in two collections according to a predicate.
	 * Keys are preserved in the resulting collections.
	 *
	 * @param callable $p The predicate on which to partition. Function is passed key, value.
	 *
	 * @return array An array with two items. The first item contains the collection
	 *               of items where the predicate returned TRUE, the second item
	 *               contains the collection of items where the predicate returned FALSE.
	 */
	public function partition($p)
	{
		$coll1 = $coll2 = array();
		foreach ($this->items as $key => $item) {
			if ($p($key, $item)) {
				$coll1[$key] = $item;
			} else {
				$coll2[$key] = $item;
			}
		}
		return array(new static($coll1), new static($coll2));
	}

	/**
	 * Clears the collection, removing all items.
	 *
	 * @return $this|Collection
	 */
	public function clear()
	{
		$this->items = array();
		return $this;
	}

	/**
	 * Extracts a slice of $length items starting at position $offset from the Collection.
	 *
	 * If $length is null it returns all items from $offset to the end of the Collection.
	 * Keys have to be preserved by this method. Calling this method will only return the
	 * selected slice and NOT change the items contained in the collection slice is called on.
	 *
	 * @param int      $offset The offset to start from.
	 * @param int|null $length The maximum number of items to return, or null for no limit.
	 *
	 * @return static|Collection
	 */
	public function slice($offset, $length = null)
	{
		return static::create(array_slice($this->items, $offset, $length, true));
	}

	/**
	 * Copies the items in this collection to a new collection.
	 *
	 * @return static|Collection
	 */
	public function copy()
	{
		return new static($this->items);
	}

	/**
	 * Copies the items in this collection to a new collection.
	 *
	 * @return static|Collection
	 */
	public function __clone() {
		return $this->copy();
	}

	/**
	 * Returns a string representation of this object.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return __CLASS__ . '@' . md5(spl_object_hash($this));
	}

	//region IteratorAggregate Methods

	public function getIterator()
	{
		return new ArrayIterator($this->items);
	}

	//endregion

	//region ArrayAccess methods

	public function offsetExists($offset)
	{
		return $this->has($offset);
	}

	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	public function offsetSet($offset, $value)
	{
		if ( ! isset($offset)) {
			$this->add($value);
			return;
		}
		$this->set($offset, $value);
	}

	public function offsetUnset($offset)
	{
		return $this->remove($offset);
	}

	//endregion

	//region Serializable Methods

	/**
	 * @param mixed $obj
	 * @return static|Collection
	 */
	public static function fromArray($obj)
	{
		return static::create($obj);
	}

	public function toJson()
	{
		return json_encode($this->toArray(), true);
	}

	/**
	 * @param string $json
	 * @return static|Collection
	 */
	public static function fromJson($json)
	{
		return static::fromArray(json_decode($json, true));
	}

	public function serialize()
	{
		return $this->toJson();
	}

	public function unserialize($serialized)
	{
		$cls = $this->fromJson($serialized);
		$this->items = $cls->toArray();
	}

	public function jsonSerialize()
	{
		return $this->toArray();
	}

	//endregion

	//region Sorting Methods

	/**
	 * Reverses the items in this collection
	 * @return $this|Collection
	 */
	public function reverse()
	{
		$this->items = array_reverse($this->items, true);
		return $this;
	}

	/**
	 * Shuffles items in this collection
	 * @return $this|Collection
	 */
	public function shuffle()
	{
		shuffle($this->items);
		return $this;
	}

	/**
	 * Sort this collection by keys
	 *
	 * If $p is true, items will be sorted from lowest to highest.
	 *
	 * If $p is false, items will be sorted in reverse order.
	 *
	 * If $p is callable, it will be called to compare the keys.
	 * The comparison function must return an integer less than, equal to,
	 * or greater than zero if the first argument is considered to be
	 * respectively less than, equal to, or greater than the second.
	 * @param bool|callable $p Sort order or user defined function
	 * @return $this|Collection
	 */
	public function sortKeys($p = true)
	{
		if (is_callable($p)) {
			uksort($this->items, $p);
		} elseif ($p) {
			ksort($this->items);
		} else {
			krsort($this->items);
		}
		return $this;
	}

	/**
	 * Sort this collection by values
	 *
	 * If $p is true, items will be sorted from lowest to highest.
	 *
	 * If $p is false, items will be sorted in reverse order.
	 *
	 * If $p is callable, it will be called to compare the values.
	 * The comparison function must return an integer less than, equal to,
	 * or greater than zero if the first argument is considered to be
	 * respectively less than, equal to, or greater than the second.
	 * @param bool|callable $p Sort order or function
	 * @return $this|Collection
	 */
	public function sort($p = true)
	{
		if (is_callable($p)) {
			usort($this->items, $p);
		} elseif ($p) {
			sort($this->items);
		} else {
			rsort($this->items);
		}
		return $this;
	}

	/**
	 * Sort this collection using a "natural order" algorithm
	 * @param bool $caseSensitive Whether to sort with case sensitivity or not
	 * @return $this|Collection
	 */
	public function sortNatural($caseSensitive = true)
	{
		if ($caseSensitive) {
			natsort($this->items);
		} else {
			natcasesort($this->items);
		}
		return $this;
	}

	//endregion

	//region Comparison Methods

	/**
	 * Computes the difference of collections using keys for comparison
	 *
	 * @param Collection|Traversable|array $values Collection to check against
	 * @param Collection|Traversable|array $_      Optional N-number of collections
	 * @param callable|null                         $p      Optionally pass a function to compare with.
	 *                                                      Function is passed a, b and must return an integer less
	 *                                                      than equal to, or greater than zero if the first argument
	 *                                                      is considered to be respectively less than, equal to, or
	 *                                                      greater than the second.
	 * @return static|Collection A collection containing all the entries from this collection that
	 *                are not present in any of the other input collections
	 */
	public function diffKeys($values, $_ = null, $p = null)
	{
		$args = static::normalizeArgs(func_get_args());
		array_unshift($args, $this->items);
		$p = end($args);
		if (is_callable($p)) {
			return new static(call_user_func_array('array_diff_ukey', $args));
		} else {
			return new static(call_user_func_array('array_diff_key', $args));
		}
	}

	/**
	 * Computes the difference of collections using values for comparison
	 *
	 * @param Collection|Traversable|array $values Collection to check against
	 * @param Collection|Traversable|array $_      Optional N-number of collections
	 * @param callable|null                         $p      Optionally pass a function to compare with.
	 *                                                      Function is passed a, b and must return an integer less
	 *                                                      than equal to, or greater than zero if the first argument
	 *                                                      is considered to be respectively less than, equal to, or
	 *                                                      greater than the second.
	 * @return static|Collection A collection containing all the entries from this collection that
	 *                are not present in any of the other input collections
	 */
	public function diff($values, $_ = null, $p = null)
	{
		$args = static::normalizeArgs(func_get_args());
		array_unshift($args, $this->items);
		$p = end($args);
		if (is_callable($p)) {
			return new static(call_user_func_array('array_udiff', $args));
		} else {
			return new static(call_user_func_array('array_diff', $args));
		}
	}

	/**
	 * Computes the intersection of collections using keys for comparison
	 *
	 * @param Collection|Traversable|array $values Collection to check against
	 * @param Collection|Traversable|array $_      Optional N-number of collections
	 * @param callable|null                         $p      Optionally pass a function to compare with.
	 *                                                      Function is passed a, b and must return an integer less
	 *                                                      than equal to, or greater than zero if the first argument
	 *                                                      is considered to be respectively less than, equal to, or
	 *                                                      greater than the second.
	 * @return static|Collection A collection containing all the entries from this collection that
	 *                are present in all of the other input collections
	 */
	public function intersectKeys($values, $_ = null, $p = null)
	{
		$args = static::normalizeArgs(func_get_args());
		array_unshift($args, $this->items);
		$p = end($args);
		if (is_callable($p)) {
			return new static(call_user_func_array('array_intersect_ukey', $args));
		} else {
			return new static(call_user_func_array('array_intersect_key', $args));
		}
	}

	/**
	 * Computes the intersection of collections using values for comparison
	 *
	 * @param Collection|Traversable|array $values Collection to check against
	 * @param Collection|Traversable|array $_      Optional N-number of collections
	 * @param callable|null                         $p      Optionally pass a function to compare with.
	 *                                                      Function is passed a, b and must return an integer less
	 *                                                      than equal to, or greater than zero if the first argument
	 *                                                      is considered to be respectively less than, equal to, or
	 *                                                      greater than the second.
	 * @return static|Collection A collection containing all the entries from this collection that
	 *                are present in all of the other input collections
	 */
	public function intersect($values, $_ = null, $p = null)
	{
		$args = static::normalizeArgs(func_get_args());
		array_unshift($args, $this->items);
		$p = end($args);
		if (is_callable($p)) {
			return new static(call_user_func_array('array_uintersect', $args));
		} else {
			return new static(call_user_func_array('array_intersect', $args));
		}
	}

	//endregion

	/**
	 * Removes duplicate values from this collection
	 * @param null $flags
	 * @return $this|Collection
	 */
	public function unique($flags = null)
	{
		$this->items = array_unique($this->items, $flags);
		return $this;
	}

	/**
	 * Calculates the sum of the values in this collection
	 * @return number The sum of values
	 */
	public function sum()
	{
		return array_sum($this->items);
	}

	/**
	 * Iteratively reduce this collection to a single value using a callback function.
	 *
	 * Function is passed $carry (previous or initial value) and $item (value of the current iteration).
	 * @param callable $func
	 * @param mixed    $initial value
	 * @return mixed The resulting value or null if collection is empty and initial is null.
	 */
	public function reduce($func, $initial = null)
	{
		return array_reduce($this->items, $func, $initial);
	}

	/**
	 * Exchanges all keys with their associated values.
	 *
	 * If a value has several occurrences, the latest key will be used as its value, and all others will be lost.
	 * @throws \Exception thrown when flip fails
	 * @return $this|Collection
	 */
	public function flip()
	{
		$arr = array_flip($this->items);
		if (!$arr) {
			throw new \Exception('Failed to flip collection');
		}
		$this->items = $arr;
		return $this;
	}

	/**
	 * Split this collection into chunks.
	 *
	 * The last chunk may contain less items.
	 *
	 * @param int $size The size of each chunk
	 * @return static|static[]|Collection|Collection[]
	 *         Returns a multidimensional collection, with each dimension containing size items
	 */
	public function chunk($size)
	{
		return static::create(array_map(array($this, 'create'), array_chunk($this->items, $size, true)));
	}

	/**
	 * Joins the values of this collection to a string
	 * @param string $delimiter The term to join on
	 * @return string A string representation of all the items in the same order,
	 *                with the delimiter between each item.
	 */
	public function join($delimiter)
	{
		return implode($delimiter, $this->items);
	}

	/**
	 * Creates a collection by using one for keys and another for its values
	 * @param Collection|Traversable|array $keys Collection of keys to be used.
	 *                                                    Illegal values for key will be converted to strings
	 * @param Collection|Traversable|array $values Collection of values to be used
	 * @return static|Collection
	 */
	public static function combine($keys, $values)
	{
		return new static(array_combine(static::normalize($keys), static::normalize($values)));
	}

	public static function isTraversable($object) {
		return is_array($object) || $object instanceof \Traversable;
	}

	public static function isArrayAccessible($object) {
		return is_array($object) || $object instanceof \ArrayAccess;
	}

	public static function className() {
		return get_called_class();
	}

	protected static function normalizeConstructorAgs($args) {
		if (count($args) == 1) {
			return static::normalize($args[0]);
		}
		return static::normalize($args);
	}

	protected static function normalizeArgs($args)
	{
		foreach ($args as &$arg) {
			$arg = static::normalize($arg);
		}
		return $args;
	}

	/**
	 * @param Collection|Traversable|array|stdClass $collection
	 * @return array
	 */
	protected static function normalize($collection)
	{
		if ($collection instanceof Collection) {
			return $collection->toArray();
		} elseif ($collection instanceof Traversable) {
			return iterator_to_array($collection, true);
		} elseif ($collection === null) {
			return array();
		} elseif (is_scalar($collection)) {
			return array($collection);
		} elseif ($collection instanceof stdClass) {
			return get_object_vars($collection);
		} else {
			return $collection;
		}
	}

	protected static function convertToCollection($arr)
	{
		$collection = new static();
		foreach ($arr as $key => $value) {
			if (static::isTraversable($value) || $value instanceof stdClass) {
				$collection[$key] = static::convertToCollection($value);
			} else {
				$collection[$key] = $value;
			}
		}
		return $collection;
	}

	protected static function convertToArray($collection)
	{
		$arr = array();
		foreach ($collection as $key => $value) {
			if (static::isTraversable($value)) {
				$arr[$key] = static::convertToArray($value);
			} else {
				$arr[$key] = $value;
			}
		}
		return $arr;
	}
}

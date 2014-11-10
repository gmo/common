<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace GMO\Common\Collections;

use ArrayIterator;
use GMO\Common\ISerializable;
use Traversable;

/**
 * An ArrayCollection is a Collection implementation that wraps a regular PHP array.
 *
 * GMO Modifications:
 *  Implementing ISerializable
 *  Modified public functions to take a callable rather than {@see \Closure}
 * 	Added default parameter to get()
 *
 * @since  2.0
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 */
class ArrayCollection implements CollectionInterface, ISerializable
{
	/** @var array An array containing the entries of this collection. */
	protected $elements;

	/**
	 * Initializes a new ArrayCollection.
	 *
	 * @param CollectionInterface|Traversable|array|mixed|null $elements
	 */
	public function __construct($elements = array())
	{
		$this->elements = static::normalizeConstructorAgs(func_get_args());
	}

	/**
	 * Initializes a new ArrayCollection.
	 *
	 * @param CollectionInterface|Traversable|array|mixed|null $elements
	 *
	 * @return static
	 */
	public static function create($elements = array())
	{
		return new static(static::normalizeConstructorAgs(func_get_args()));
	}

	public function toArray()
	{
		return $this->elements;
	}

	public function first()
	{
		return reset($this->elements);
	}

	public function last()
	{
		return end($this->elements);
	}

	public function key()
	{
		return key($this->elements);
	}

	public function next()
	{
		return next($this->elements);
	}

	public function current()
	{
		return current($this->elements);
	}

	public function remove($key)
	{
		if (isset($this->elements[$key]) || array_key_exists($key, $this->elements)) {
			$removed = $this->elements[$key];
			unset($this->elements[$key]);

			return $removed;
		}

		return null;
	}

	/** @inheritdoc */
	public function removeElement($element)
	{
		$key = array_search($element, $this->elements, true);

		if ($key !== false) {
			$value = $this->elements[$key];
			unset($this->elements[$key]);
			return $value;
		}

		return null;
	}

	public function removeFirst()
	{
		return $this->removeElement($this->first());
	}

	public function removeLast()
	{
		return $this->removeElement($this->last());
	}

	public function containsKey($key)
	{
		return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
	}

	public function contains($element)
	{
		return in_array($element, $this->elements, true);
	}

	public function exists($p)
	{
		foreach ($this->elements as $key => $element) {
			if ($p($key, $element)) {
				return true;
			}
		}
		return false;
	}

	public function indexOf($element)
	{
		return array_search($element, $this->elements, true);
	}

	public function get($key, $default = null)
	{
		if (isset($this->elements[$key])) {
			return $this->elements[$key];
		}
		return $default;
	}

	public function getKeys()
	{
		return static::create(array_keys($this->elements));
	}

	public function getValues()
	{
		return static::create(array_values($this->elements));
	}

	public function count()
	{
		return count($this->elements);
	}

	/**
	 * @inheritdoc
	 * @return $this
	 */
	public function set($key, $value)
	{
		$this->elements[$key] = $value;
		return $this;
	}

	/**
	 * @inheritdoc
	 * @return $this
	 */
	public function add($value)
	{
		$this->elements[] = $value;
		return $this;
	}

	/**
	 * @inheritdoc
	 * @return $this
	 */
	public function prepend($value)
	{
		array_unshift($this->elements, $value);
		return $this;
	}

	/**
	 * Replaces elements in this collection from another collection by comparing keys
	 * @param CollectionInterface|Traversable|array $collection The collection from which elements will be extracted.
	 * @param CollectionInterface|Traversable|array $_          Optional N-number of collections
	 * @return $this
	 */
	public function replace($collection, $_ = null)
	{
		$args = static::normalizeArgs(func_get_args());
		array_unshift($args, $this->elements);
		$this->elements = call_user_func_array('array_replace', $args);
		return $this;
	}

	/**
	 * Merges elements from another collection into this collection.
	 * If the collections have string keys, the value from the input
	 * collection will replace the current value. If the collections
	 * have numeric keys, the input collection will be appended to
	 * this collection.
	 *
	 * @param CollectionInterface|Traversable|array $collection The collection from which elements will be extracted.
	 * @param CollectionInterface|Traversable|array $_          Optional N-number of collections
	 * @return $this
	 */
	public function merge($collection, $_ = null)
	{
		$args = static::normalizeArgs(func_get_args());
		array_unshift($args, $this->elements);
		$this->elements = call_user_func_array('array_merge', $args);
		return $this;
	}

	public function isEmpty()
	{
		return ! $this->elements;
	}

	/**
	 * @inheritdoc
	 * @return static
	 */
	public function map($func)
	{
		return static::create(array_map($func, $this->elements));
	}

	/**
	 * @inheritdoc
	 * @return static
	 */
	public function filter($p)
	{
		if ($p === null) {
			return static::create(array_filter($this->elements));
		}
		$func = new \ReflectionFunction($p);
		$params = $func->getParameters();
		if (count($params) === 1 && $params[0]->getName() !== 'key') {
			return static::create(array_filter($this->elements, $p));
		}

		$newElements = new static();
		foreach ($this->elements as $key => $value) {
			if ($p($key, $value)) {
				$newElements->set($key, $value);
			}
		}
		return $newElements;

	}

	public function forAll($p)
	{
		foreach ($this->elements as $key => $element) {
			if ( ! $p($key, $element)) {
				return false;
			}
		}

		return true;
	}

	public function partition($p)
	{
		$coll1 = $coll2 = array();
		foreach ($this->elements as $key => $element) {
			if ($p($key, $element)) {
				$coll1[$key] = $element;
			} else {
				$coll2[$key] = $element;
			}
		}
		return array(new static($coll1), new static($coll2));
	}

	/**
	 * @inheritdoc
	 * @return $this
	 */
	public function clear()
	{
		$this->elements = array();
		return $this;
	}

	/**
	 * @inheritdoc
	 * @return static
	 */
	public function slice($offset, $length = null)
	{
		return static::create(array_slice($this->elements, $offset, $length, true));
	}

	/**
	 * Copies the elements in this collection to a new collection.
	 *
	 * @return $this
	 */
	public function copy()
	{
		return new static($this->elements);
	}

	/**
	 * Copies the elements in this collection to a new collection.
	 *
	 * @return $this
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
		return new ArrayIterator($this->elements);
	}

	//endregion

	//region ArrayAccess methods

	public function offsetExists($offset)
	{
		return $this->containsKey($offset);
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
	 * @return $this
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
	 * @return $this
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
		$this->elements = $cls->toArray();
	}

	public function jsonSerialize()
	{
		return $this->toArray();
	}

	//endregion

	//region Sorting Methods

	/**
	 * Reverses the elements in this collection
	 * @return $this
	 */
	public function reverse()
	{
		$this->elements = array_reverse($this->elements, true);
		return $this;
	}

	/**
	 * Shuffles elements in this collection
	 * @return $this
	 */
	public function shuffle()
	{
		shuffle($this->elements);
		return $this;
	}

	/**
	 * Sort this collection by keys
	 *
	 * If $p is true, elements will be sorted from lowest to highest.
	 *
	 * If $p is false, elements will be sorted in reverse order.
	 *
	 * If $p is callable, it will be called to compare the keys.
	 * The comparison function must return an integer less than, equal to,
	 * or greater than zero if the first argument is considered to be
	 * respectively less than, equal to, or greater than the second.
	 * @param bool|callable $p Sort order or user defined function
	 * @return $this
	 */
	public function sortKeys($p = true)
	{
		if (is_callable($p)) {
			uksort($this->elements, $p);
		} elseif ($p) {
			ksort($this->elements);
		} else {
			krsort($this->elements);
		}
		return $this;
	}

	/**
	 * Sort this collection by values
	 *
	 * If $p is true, elements will be sorted from lowest to highest.
	 *
	 * If $p is false, elements will be sorted in reverse order.
	 *
	 * If $p is callable, it will be called to compare the values.
	 * The comparison function must return an integer less than, equal to,
	 * or greater than zero if the first argument is considered to be
	 * respectively less than, equal to, or greater than the second.
	 * @param bool|callable $p Sort order or function
	 * @return $this
	 */
	public function sortValues($p = true)
	{
		if (is_callable($p)) {
			usort($this->elements, $p);
		} elseif ($p) {
			sort($this->elements);
		} else {
			rsort($this->elements);
		}
		return $this;
	}

	/**
	 * Sort this collection using a "natural order" algorithm
	 * @param bool $caseSensitive Whether to sort with case sensitivity or not
	 * @return $this
	 */
	public function sortNatural($caseSensitive = true)
	{
		if ($caseSensitive) {
			natsort($this->elements);
		} else {
			natcasesort($this->elements);
		}
		return $this;
	}

	//endregion

	//region Comparison Methods

	/**
	 * Computes the difference of collections using keys for comparison
	 *
	 * @param CollectionInterface|Traversable|array $values Collection to check against
	 * @param CollectionInterface|Traversable|array $_      Optional N-number of collections
	 * @param callable|null                         $p      Optionally pass a function to compare with.
	 *                                                      Function is passed a, b and must return an integer less
	 *                                                      than equal to, or greater than zero if the first argument
	 *                                                      is considered to be respectively less than, equal to, or
	 *                                                      greater than the second.
	 * @return static A collection containing all the entries from this collection that
	 *                are not present in any of the other input collections
	 */
	public function diffKeys($values, $_ = null, $p = null)
	{
		$args = static::normalizeArgs(func_get_args());
		array_unshift($args, $this->elements);
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
	 * @param CollectionInterface|Traversable|array $values Collection to check against
	 * @param CollectionInterface|Traversable|array $_      Optional N-number of collections
	 * @param callable|null                         $p      Optionally pass a function to compare with.
	 *                                                      Function is passed a, b and must return an integer less
	 *                                                      than equal to, or greater than zero if the first argument
	 *                                                      is considered to be respectively less than, equal to, or
	 *                                                      greater than the second.
	 * @return static A collection containing all the entries from this collection that
	 *                are not present in any of the other input collections
	 */
	public function diffValues($values, $_ = null, $p = null)
	{
		$args = static::normalizeArgs(func_get_args());
		array_unshift($args, $this->elements);
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
	 * @param CollectionInterface|Traversable|array $values Collection to check against
	 * @param CollectionInterface|Traversable|array $_      Optional N-number of collections
	 * @param callable|null                         $p      Optionally pass a function to compare with.
	 *                                                      Function is passed a, b and must return an integer less
	 *                                                      than equal to, or greater than zero if the first argument
	 *                                                      is considered to be respectively less than, equal to, or
	 *                                                      greater than the second.
	 * @return static A collection containing all the entries from this collection that
	 *                are present in all of the other input collections
	 */
	public function intersectKeys($values, $_ = null, $p = null)
	{
		$args = static::normalizeArgs(func_get_args());
		array_unshift($args, $this->elements);
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
	 * @param CollectionInterface|Traversable|array $values Collection to check against
	 * @param CollectionInterface|Traversable|array $_      Optional N-number of collections
	 * @param callable|null                         $p      Optionally pass a function to compare with.
	 *                                                      Function is passed a, b and must return an integer less
	 *                                                      than equal to, or greater than zero if the first argument
	 *                                                      is considered to be respectively less than, equal to, or
	 *                                                      greater than the second.
	 * @return static A collection containing all the entries from this collection that
	 *                are present in all of the other input collections
	 */
	public function intersectValues($values, $_ = null, $p = null)
	{
		$args = static::normalizeArgs(func_get_args());
		array_unshift($args, $this->elements);
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
	 * @return $this
	 */
	public function unique($flags = null)
	{
		$this->elements = array_unique($this->elements, $flags);
		return $this;
	}

	/**
	 * Calculates the sum of the values in this collection
	 * @return number The sum of values
	 */
	public function sum()
	{
		return array_sum($this->elements);
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
		return array_reduce($this->elements, $func, $initial);
	}

	/**
	 * Exchanges all keys with their associated values.
	 *
	 * If a value has several occurrences, the latest key will be used as its value, and all others will be lost.
	 * @throws \Exception thrown when flip fails
	 * @return $this
	 */
	public function flip()
	{
		$arr = array_flip($this->elements);
		if (!$arr) {
			throw new \Exception('Failed to flip collection');
		}
		$this->elements = $arr;
		return $this;
	}

	/**
	 * Split this collection into chunks.
	 *
	 * The last chunk may contain less elements.
	 *
	 * @param int $size The size of each chunk
	 * @return static|static[] Returns a multidimensional collection, with each dimension containing size elements
	 */
	public function chunk($size)
	{
		return static::create(array_map(array($this, 'create'), array_chunk($this->elements, $size, true)));
	}

	/**
	 * Joins the values of this collection to a string
	 * @param string $delimiter The term to join on
	 * @return string A string representation of all the elements in the same order,
	 *                with the delimiter between each element.
	 */
	public function join($delimiter)
	{
		return implode($delimiter, $this->elements);
	}

	/**
	 * Creates a collection by using one for keys and another for its values
	 * @param CollectionInterface|Traversable|array $keys Collection of keys to be used.
	 *                                                    Illegal values for key will be converted to strings
	 * @param CollectionInterface|Traversable|array $values Collection of values to be used
	 * @return static
	 */
	public static function combine($keys, $values)
	{
		return new static(array_combine(static::normalize($keys), static::normalize($values)));
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
	 * @param ArrayCollection|Traversable|array $collection
	 * @return array
	 */
	protected static function normalize($collection)
	{
		if ($collection instanceof ArrayCollection) {
			return $collection->toArray();
		} elseif ($collection instanceof Traversable) {
			return iterator_to_array($collection, true);
		} elseif ($collection === null) {
			return array();
		} elseif (is_scalar($collection)) {
			return array($collection);
		} else {
			return $collection;
		}
	}
}

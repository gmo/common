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
	 * @param array $elements
	 */
	public function __construct(array $elements = array())
	{
		$this->elements = $elements;
	}

	/**
	 * Initializes a new ArrayCollection.
	 *
	 * @param array $elements
	 *
	 * @return $this
	 */
	public static function create(array $elements = array())
	{
		return new static($elements);
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

	public function removeElement($element)
	{
		$key = array_search($element, $this->elements, true);

		if ($key !== false) {
			unset($this->elements[$key]);

			return true;
		}

		return false;
	}

	public function removeFirst()
	{
		$this->removeElement($this->first());
	}

	public function removeLast()
	{
		$this->removeElement($this->last());
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
		return array_keys($this->elements);
	}

	public function getValues()
	{
		return array_values($this->elements);
	}

	public function count()
	{
		return count($this->elements);
	}

	public function set($key, $value)
	{
		$this->elements[$key] = $value;
	}

	public function add($value)
	{
		$this->elements[] = $value;
		return true;
	}

	public function prepend($value)
	{
		array_unshift($this->elements, $value);
		return true;
	}

	/**
	 * Replaces elements in this collection from array(s)
	 * @param array $values The array from which elements will be extracted.
	 * @param array $_ Optional N-number of arrays
	 */
	public function replace(array $values, array $_ = null)
	{
		$args = func_get_args();
		array_unshift($args, $this->elements);
		$this->elements = call_user_func_array('array_replace', $args);
	}

	public function isEmpty()
	{
		return ! $this->elements;
	}

	public function map($func)
	{
		return new static(array_map($func, $this->elements));
	}

	public function filter($p)
	{
		if ($p === null) {
			return new static(array_filter($this->elements));
		}
		return new static(array_filter($this->elements, $p));
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

	public function clear()
	{
		$this->elements = array();
	}

	public function slice($offset, $length = null)
	{
		return new static(array_slice($this->elements, $offset, $length, true));
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
		}
		$this->set($offset, $value);
	}

	public function offsetUnset($offset)
	{
		return $this->remove($offset);
	}

	//endregion

	//region Serializable Methods

	public static function fromArray($obj)
	{
		return static::create($obj);
	}

	public function toJson()
	{
		return json_encode($this->toArray(), true);
	}

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
}

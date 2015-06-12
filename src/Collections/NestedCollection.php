<?php
namespace Gmo\Common\Collections;

class NestedCollection extends Collection {

	/**
	 * Gets the item at the specified path.
	 *
	 * Example:
	 *
	 *    $c = new NestedCollection([
	 *        'foo' => [
	 *            'bar' => 'hello world',
	 *        ],
	 *    ]);
	 *
	 *    $c->get('foo/bar'); // Returns 'hello world'
	 *
	 * @param int|string $path Path to the key of item to retrieve
	 * @param mixed|null $default The default valud to return if not found
	 *
	 * @return mixed
	 */
	public function get($path, $default = null) {
		$parts = explode('/', $path);
		if (count($parts) <= 1) {
			return parent::get($path);
		}

		$value = $this;
		while (null !== ($part = array_shift($parts))) {
			if (!static::isArrayAccessible($value) || !isset($value[$part])) {
				return $default;
			}
			$value = $value[$part];
		}

		return $value;
	}

	public function has($path) {
		$parts = explode('/', $path);
		if (count($parts) <= 1) {
			return parent::has($path);
		}

		$value = $this;
		while (null !== ($part = array_shift($parts))) {
			if (!static::isArrayAccessible($value) || !isset($value[$part])) {
				return false;
			}
			$value = $value[$part];
		}
		return true;
	}

	/**
	 * Set a value in a nested collection key.
	 * Sub-collections will be created as needed to set the value.
	 *
	 * A value may be appended by using "[]".
	 *
	 * Nested set example:
	 *
	 *    $c = new NestedCollection();
	 *    $c->set('foo/bar', 'world');
	 *    $c->get('foo'); // Returns ['bar' => 'world']
	 *
	 * Nested append example:
	 *
	 *    $c->set('foo/bar/[]', 'world');
	 *    $c->get('foo'); // Returns ['bar' => ['world']]
	 *
	 * @param int|string $path Path to key
	 * @param mixed      $value Value to set at the key
	 *
	 * @return $this|NestedCollection
	 *
	 * @throws \RuntimeException When trying to set a path that travels through
	 *                           a scalar value or an array under an object.
	 */
	public function set($path, $value) {
		// This provides consistent functionality.
		// Instead of saying [] can only be used in a sub-path
		if ($path === '[]') {
			return $this->add($value);
		}

		$parts = explode('/', $path);
		// If not nested path, use normal set
		if (count($parts) <= 1) {
			return parent::set($path, $value);
		}

		$previousKey = null;
		$current = $this;
		while(null !== ($key = array_shift($parts))) {
			// Check if current is not array accessible
			if (!static::isArrayAccessible($current)) {
				throw new \RuntimeException("Trying to set path {$path}, " .
					"but {$previousKey} is set and is not array accessible");
			}
			$previousKey = $key;

			// If last part in path, set value at key
			if (empty($parts)) {
				if ($key === '[]') {
					$current[] = $value;
				} else {
					$current[$key] = $value;
				}
			// Sub-collection exists, so get it and continue looping
			} elseif (isset($current[$key])) {
				$current = &$this->getSubCollection($current, $key);
			// Create sub-collection and continue looping
			} else {
				$new = new static();
				if ($key === '[]') {
					$current[] = $new;
				} else {
					$current[$key] = $new;
				}
				unset($current);
				$current = $new;
				unset($new);
			}
		}

		return $this;
	}

	public function remove($path) {
		$parts = explode('/', $path);
		if (count($parts) <= 1) {
			return parent::remove($path);
		}

		// Loop to collection above item to remove
		$previousKey = null;
		$current = $this;
		while (count($parts) > 1) {
			$key = array_shift($parts);

			if (!static::isArrayAccessible($current)) {
				throw new \RuntimeException("Trying to remove path {$path}, " .
					"but {$previousKey} is not array accessible");
			}
			$previousKey = $key;

			if (!isset($current[$key])) {
				return null;
			}

			$current = &$this->getSubCollection($current, $key);

		}

		// $key currently is key of current collection.
		// We need the key of the item to remove in current collection.
		$key = array_shift($parts);

		if (!static::isArrayAccessible($current)) {
			throw new \RuntimeException("Trying to remove path {$path}, " .
					"but {$previousKey} is not array accessible");
		}

		if ($current instanceof Collection) {
			return $current->remove($key);
		}
		unset($current[$key]);
		return null;
	}

	protected function &getSubCollection(&$current, $key) {
		// If the value to get is an array we need to pass it by reference,
		// so modifications applied are saved in the parent.
		if (is_array($current[$key])) {
			if ($current instanceof Collection) {
				$current = &$current->items[$key];
			} elseif (is_array($current)) {
				$current = &$current[$key];
			} else {
				throw new \RuntimeException(
					'Cannot modify an array that is under a ArrayAccess object (except for ArrayCollection).'
				);
			}
			// Objects don't need this as they are automatically passed by reference.
		} else {
			// We are just changing pointers here.
			// We don't want to overwrite a collection with the sub-collection
			$sub = $current[$key];
			unset($current);
			$current = $sub;
			unset($sub);
		}

		return $current;
	}
}

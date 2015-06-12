<?php
namespace Gmo\Common\Collections;

use ArrayAccess;
use Countable;
use Gmo\Common\Serialization\SerializableInterface;
use IteratorAggregate;

interface CollectionInterface extends Countable, IteratorAggregate, ArrayAccess, SerializableInterface
{
	public function remove($key);

	public function removeItem($item);

	public function has($key);

	public function hasItem($item);

	public function exists($p);

	public function get($key, $default = null);

	public function set($key, $value);

	public function count();

	public function isEmpty();

	public function map($callback);

	public function filter($p);

	public function clear();

	public function slice($offset, $length = null);

	public function sort($p = true);

	public function sortNatural($caseSensitive = true);

	public function diff($values, $p = null);

	public function intersect($values, $p = null);

	public function unique($flags = null);

	public function chunk($size);
}

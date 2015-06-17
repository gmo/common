<?php
namespace Gmo\Common\Collections;

interface HashMapInterface extends CollectionInterface
{
	public function keys();

	public function values();

	public function replace($collection);

	public function replaceRecursive($collection);

	public function defaults($collection);

	public function sortKeys($p = true);

	public function diffKeys($values, $p = null);

	public function intersectKeys($values, $p = null);

	public function flip();
}

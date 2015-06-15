<?php
namespace Gmo\Common\Collections;

/**
 * Because List is a reserved word
 */
interface Sequence extends CollectionInterface
{
	public function first();

	public function last();

	public function removeFirst();

	public function removeLast();

	public function indexOf($item);

	public function add($item);

	public function prepend($value);

	public function merge($collection);

	public function mergeRecursive($collection);

	public function reverse();

	public function shuffle();

	public function sum();

	public function reduce($func, $initial = null);

	public function join($delimiter);
}

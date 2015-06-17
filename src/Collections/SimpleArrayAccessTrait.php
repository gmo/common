<?php
namespace Gmo\Common\Collections;

/**
 * Forwards ArrayAccess to SimpleAccess methods
 */
trait SimpleArrayAccessTrait
{
	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset) {
		return $this->has($offset);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset) {
		return $this->get($offset);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value) {
		$this->set($offset, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset) {
		$this->remove($offset);
	}
}

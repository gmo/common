<?php
namespace Gmo\Common\Collections;

/**
 * TODO: Should extend ArrayAccess?
 */
interface SimpleAccess {

	public function get($key, $default = null);

	public function has($key);

	public function set($key, $value);

	public function remove($key, $default = null);
}

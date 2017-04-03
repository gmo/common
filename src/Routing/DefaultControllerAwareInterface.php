<?php
namespace Gmo\Common\Routing;

/**
 * @deprecated since 1.30 will be removed in 2.0. Use {@see Gmo\Web\Routing\DefaultControllerAwareInterface} instead.
 */
interface DefaultControllerAwareInterface {

	/**
	 * Sets the default controller class.
	 *
	 * This is this first part of a callable so it can be a string or an object.
	 *
	 * @param string|object $class
	 */
	public function setDefaultControllerClass($class);

	/**
	 * Sets the default controller class method.
	 *
	 * This is this second part of a callable.
	 *
	 * @param string $method
	 */
	public function setDefaultControllerMethod($method);
}

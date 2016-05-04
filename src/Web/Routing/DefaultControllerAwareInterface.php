<?php
namespace Gmo\Common\Web\Routing;

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

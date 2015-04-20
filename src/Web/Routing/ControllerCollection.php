<?php
namespace Routing;

use GMO\Common\String;
use GMO\Common\Web\Routing\DefaultControllerAwareInterface;
use Silex;

/**
 * Extended ControllerCollection for these reasons:
 *
 * - Allow a route that starts with :: to go to the method specified in the current class.
 * - Allow a null route to default to specified class method.
 * - Remove trailing slash from flushed routes
 */
class ControllerCollection extends Silex\ControllerCollection implements DefaultControllerAwareInterface {

	/**
	 * This uses default class/method if not provided
	 *
	 * {@inheritdoc}
	 */
	public function match($pattern, $to = null) {
		if (!$this->defaultControllerClass) {
			return parent::match($pattern, $to);
		}
		if ($to === null && $this->defaultControllerMethod) {
			$to = [$this->defaultControllerClass, $this->defaultControllerMethod];
		} elseif (is_string($to) && String::startsWith($to, '::')) {
			$to = [$this->defaultControllerClass, substr($to, 2)];
		}
		return parent::match($pattern, $to);
	}

	/**
	 * When mounting a controller class with a prefix most times you have a route with a blank path.
	 * That is the only route that flushes to include an (unwanted) trailing slash.
	 *
	 * This fixes that trailing slash.
	 *
	 * @param string $prefix
	 * @return \Symfony\Component\Routing\RouteCollection
	 */
	public function flush($prefix = '')
	{
		$routes = parent::flush($prefix);
		foreach ($routes->all() as $name => $route) {
			if (substr($route->getPath(), -1) === '/') {
				$route->setPath(rtrim($route->getPath(), '/'));
			}
		}
		return $routes;
	}

	public function setDefaultControllerClass($class) {
		$this->defaultControllerClass = $class;
	}

	public function setDefaultControllerMethod($method) {
		$this->defaultControllerMethod = $method;
	}

	protected $defaultControllerClass;
	protected $defaultControllerMethod;
}

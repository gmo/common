<?php
namespace GMO\Common\Web\Routing;

use GMO\Common\String;
use Silex;
use Silex\Controller;
use Symfony\Component\Routing\RouteCollection;

/**
 * Extended ControllerCollection for these reasons:
 *
 * - Allow a route that starts with :: to go to the method specified in the current class.
 * - Allow a null route to default to specified class method.
 * - Split flush method to make it easier to override
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
	 * {@inheritdoc}
	 *
	 * This is similar logic to parent class just broken up into
	 * multiple methods to make it easier to override.
	 */
	public function flush($prefix = '')
	{
		$routes = new RouteCollection();

		$prefix = $this->normalizePrefix($prefix);

		foreach ($this->controllers as $controller) {
			if ($controller instanceof Controller) {
				$this->flushController($routes, $controller, $prefix);
			} elseif ($controller instanceof Silex\ControllerCollection) {
				$this->flushControllerCollection($routes, $controller, $prefix);
			} else {
				throw new \LogicException('Controllers need to be Controller or ControllerCollection instances');
			}
		}

		// RouteCollection::addPrefix is intentionally not called here.
		// The prefix should be added in flushController method.

		$this->controllers = array();

		return $routes;
	}

	public function setDefaultControllerClass($class) {
		$this->defaultControllerClass = $class;
	}

	public function setDefaultControllerMethod($method) {
		$this->defaultControllerMethod = $method;
	}

	/**
	 * Add the Controller to the RouteCollection and freeze it
	 *
	 * @param RouteCollection $routes
	 * @param Controller      $controller
	 * @param string          $prefix
	 */
	protected function flushController(RouteCollection $routes, Controller $controller, $prefix) {
		// When mounting a controller class with a prefix most times you have a route with a blank path.
		// That is the only route that flushes to include an (unwanted) trailing slash.
		// This fixes that trailing slash.
		$controller->getRoute()->setPath(rtrim($prefix . $controller->getRoute()->getPath(), '/'));

		$this->generateControllerName($routes, $controller, $prefix);
		$routes->add($controller->getRouteName(), $controller->getRoute());
		$controller->freeze();
	}

	/**
	 * Add the ControllerCollection to the RouteCollection
	 *
	 * @param RouteCollection            $routes
	 * @param Silex\ControllerCollection $collection
	 * @param string                     $prefix
	 */
	protected function flushControllerCollection(RouteCollection $routes, Silex\ControllerCollection $collection, $prefix) {
		$prefix .= $this->normalizePrefix($collection->prefix);
		$routes->addCollection($collection->flush($prefix));
	}

	/**
	 * Same logic as the first part of {@see RouteCollection::addPrefix}
	 *
	 * @param $prefix
	 *
	 * @return string
	 */
	protected function normalizePrefix($prefix) {
		$prefix = trim(trim($prefix), '/');
		if (!empty($prefix)) {
			$prefix = '/' . $prefix;
		}
		return $prefix;
	}

	/**
	 * Generate route name for controller if one does not exist
	 *
	 * Note: same code as in {@see Silex\ControllerCollection::flush}
	 *
	 * @param RouteCollection $routes
	 * @param Controller      $controller
	 * @param string          $prefix
	 */
	protected function generateControllerName(RouteCollection $routes, Controller $controller, $prefix) {
		if (!$name = $controller->getRouteName()) {
			$name = $controller->generateRouteName($prefix);
			while($routes->get($name)) {
				$name .= '_';
			}
			$controller->bind($name);
		}
	}

	protected $defaultControllerClass;
	protected $defaultControllerMethod;
}

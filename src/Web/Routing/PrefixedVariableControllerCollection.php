<?php
namespace GMO\Common\Web\Routing;

use Silex;
use Silex\Controller;
use Symfony\Component\Routing\RouteCollection;

/**
 * Extends ControllerCollection to automatically:
 * 1) Prefix all routes with a variable
 * 2) Create a separate variable-less route for each route
 *
 * The bound route name is on the route with the prefix.
 *
 * Note:
 */
abstract class PrefixedVariableControllerCollection extends ControllerCollection {

	/** @var string|null The requirement for the variable */
	protected $variableRequirement = null;

	/**
	 * Returns the variable name
	 *
	 * @return string
	 */
	abstract protected function getVariableName();

	protected function flushController($prefix, Controller $controller, RouteCollection $routes) {
		if ($this->variableRequirement) {
			$controller->assert($this->getVariableName(), $this->variableRequirement);
		}

		// Clone current controller for unprefixed route
		$unprefixedController = new Controller(clone $controller->getRoute());

		parent::flushController($this->getVariablePrefix($prefix), $controller, $routes);
		parent::flushController($prefix, $unprefixedController, $routes);

		// Set real route name that will be used if route is matched
		$unprefixedController->getRoute()->setDefault(sprintf('_prefixed_route', $this->getVariableName()), $controller->getRouteName());
	}

	protected function getVariablePrefix($prefix) {
		return sprintf('/{%s}%s', $this->getVariableName(), $prefix);
	}

	/**
	 * Flushes the sub-collection with current class logic instead of its own
	 *
	 * @param string               $prefix
	 * @param ControllerCollection $collection
	 * @param RouteCollection      $routes
	 */
	protected function flushSubCollection($prefix, ControllerCollection $collection, RouteCollection $routes) {
		$prefix .= $this->normalizePrefix($collection->prefix);
		$this->flushCollection($prefix, $collection, $routes);
	}
}

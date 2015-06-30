<?php
namespace GMO\Common\Web\Routing;

use Silex\Controller;
use Symfony\Component\Routing\RouteCollection;

/**
 * Extends ControllerCollection to automatically:
 * 1) Prefix all routes with a variable
 * 2) Create a separate variable-less route for each route
 *
 * The bound route name is on the route with the prefix
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

	protected function flushController(RouteCollection $routes, Controller $controller, $prefix) {
		if ($this->variableRequirement) {
			$controller->assert($this->getVariableName(), $this->variableRequirement);
		}

		// Clone current controller for unprefixed route
		$unprefixedController = new Controller(clone $controller->getRoute());

		parent::flushController($routes, $controller, $this->getVariablePrefix($prefix));
		parent::flushController($routes, $unprefixedController, $prefix);

		// Set real route name that will be used if route is matched
		$unprefixedController->getRoute()->setDefault(sprintf('_prefixed_route', $this->getVariableName()), $controller->getRouteName());
	}

	protected function getVariablePrefix($prefix) {
		return sprintf('/{%s}%s', $this->getVariableName(), $prefix);
	}
}

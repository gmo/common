<?php
namespace GMO\Common\Web\Routing;

use Silex\Route;

/**
 * Extends ControllerCollection to automatically:
 * 1) Prefix all routes with locale variable
 * 2) Create a separate "locale-less" route for each route
 *
 * The bound route name is on the route with the locale prefix.
 */
class LocaleControllerCollection extends PrefixedVariableControllerCollection {

	public function __construct(Route $defaultRoute, $supportedLocales = array()) {
		parent::__construct($defaultRoute);
		$this->variableRequirement = implode('|', $supportedLocales);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getVariableName() {
		return '_locale';
	}
}

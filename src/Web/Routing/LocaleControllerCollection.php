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

	/**
	 * LocaleControllerCollection constructor.
	 *
	 * @param Route        $defaultRoute
	 * @param array|string $supportedLocales Regex requirement for locale, or a list of locales
	 */
	public function __construct(Route $defaultRoute, $supportedLocales = '[a-zA-Z]{2}') {
		$requirement = is_array($supportedLocales) ? implode('|', $supportedLocales) : $supportedLocales;
		parent::__construct($defaultRoute, $requirement);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getVariableName() {
		return '_locale';
	}
}

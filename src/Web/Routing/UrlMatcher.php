<?php
namespace GMO\Common\Web\Routing;

use Symfony\Component\Routing\Matcher\UrlMatcher as UrlMatcherBase;
use Symfony\Component\Routing\Route;

/**
 * {@inheritdoc}
 *
 * Trailing slash is removed before matching.
 */
class UrlMatcher extends UrlMatcherBase {

	public function match($pathinfo) {
		// Remove trailing slash
		if ($pathinfo !== '/') {
			$pathinfo = rtrim($pathinfo, '/');
		}

		return parent::match($pathinfo);
	}

	/**
	 * {@inheritdoc}
	 *
	 * Overrides the route name of unprefixed routes with the original/prefixed route name
	 *
	 * @see PrefixedVariableControllerCollection
	 */
	protected function getAttributes(Route $route, $name, array $attributes) {
		$attrs = parent::getAttributes($route, $name, $attributes);

		if (isset($attrs['_prefixed_route'])) {
			$name = $attrs['_prefixed_route'];
			$attrs['_route'] = $name;
			unset($attributes['_prefixed_route']);
		}

		return $attrs;
	}
}

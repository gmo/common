<?php
namespace GMO\Common\Web\Routing;

use Silex\RedirectableUrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Route;

/**
 * Matches routes with or without trailing slash but does not redirect for performance
 */
class UrlMatcher extends RedirectableUrlMatcher {

	public function match($pathinfo) {
		try {
			return parent::match($pathinfo);
		} catch (ResourceNotFoundException $e) {
			if (!in_array($this->context->getMethod(), array('HEAD', 'GET'))) {
				throw $e;
			}
		}

		// Try matching the route with trailing slash
		if ('/' !== substr($pathinfo, -1)) {
			try {
				return parent::match($pathinfo.'/');
			} catch (ResourceNotFoundException $e2) {
				throw $e;
			}
		}

		// Try matching the route without trailing slash
		$withoutTrailingSlash = substr($pathinfo, 0, -1);
		try {
			return parent::match($withoutTrailingSlash);
		} catch (ResourceNotFoundException $e2) {
			throw $e;
		}
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

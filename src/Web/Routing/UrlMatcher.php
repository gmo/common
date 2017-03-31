<?php
namespace GMO\Common\Web\Routing;

use Silex\RedirectableUrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher as UrlMatcherBase;
use Symfony\Component\Routing\Route;

/**
 * {@inheritdoc}
 *
 * Matches routes with or without trailing slash but does not redirect for performance.
 *
 * @deprecated since 1.30 will be removed in 2.0. Use {@see Gmo\Web\Routing\UrlMatcher} instead.
 */
class UrlMatcher extends RedirectableUrlMatcher
{
    /**
     * {@inheritdoc}
     */
    public function match($pathinfo)
    {
        // Try matching the route without trailing slash first as this is likely to be the case.
        $pathinfo = rtrim($pathinfo, '/');
        try {
            /*
             * RedirectableUrlMatcher attempts to match a trailing slash
             * on matching failure and then (if found) redirect to it.
             * We want to skip the redirect and just match it in this
             * request for performance. So our parent calls skip this
             * class and go straight to Symfony's UrlMatcher. We still
             * want to extend this class though, since it will redirect
             * for mismatched scheme requirements.
             */
            return UrlMatcherBase::match($pathinfo);
        } catch (ResourceNotFoundException $e) {
        }

        // Next try with trailing slash
        try {
            return UrlMatcherBase::match($pathinfo . '/');
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
    protected function getAttributes(Route $route, $name, array $attributes)
    {
        $attrs = parent::getAttributes($route, $name, $attributes);

        if (isset($attrs['_prefixed_route'])) {
            $attrs['_route'] = $attrs['_prefixed_route'];
            unset($attrs['_prefixed_route']);
        }

        return $attrs;
    }
}

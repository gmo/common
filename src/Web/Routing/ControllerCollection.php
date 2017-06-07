<?php

namespace GMO\Common\Web\Routing;

use GMO\Common\Str;
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
 *
 * @deprecated since 1.30 will be removed in 2.0. Use {@see Gmo\Web\Routing\ControllerCollection} instead.
 */
class ControllerCollection extends Silex\ControllerCollection implements DefaultControllerAwareInterface
{
    protected $defaultControllerClass;
    protected $defaultControllerMethod;

    /**
     * This uses default class/method if not provided
     *
     * {@inheritdoc}
     */
    public function match($pattern, $to = null)
    {
        if (!$this->defaultControllerClass) {
            return parent::match($pattern, $to);
        }
        if ($to === null && $this->defaultControllerMethod) {
            $to = array($this->defaultControllerClass, $this->defaultControllerMethod);
        } elseif (is_string($to) && Str::startsWith($to, '::')) {
            $to = array($this->defaultControllerClass, substr($to, 2));
        }

        return parent::match($pattern, $to);
    }

    public function setDefaultControllerClass($class)
    {
        $this->defaultControllerClass = $class;
    }

    public function setDefaultControllerMethod($method)
    {
        $this->defaultControllerMethod = $method;
    }

    /**
     * {@inheritdoc}
     */
    public function flush($prefix = '')
    {
        return $this->flushCollection($prefix, $this, new RouteCollection());
    }

    /**
     * Persists and freezes staged controllers.
     *
     * Note: This is similar logic to {@see \Silex\ControllerCollection::doFlush} just broken up into
     * multiple methods to make it easier to override.
     *
     * Note: This method has no side effects.
     *
     * @param string               $prefix
     * @param ControllerCollection $collection
     * @param RouteCollection      $routes
     *
     * @return RouteCollection
     */
    protected function flushCollection($prefix, ControllerCollection $collection, RouteCollection $routes)
    {
        $prefix = $this->normalizePrefix($prefix);

        foreach ($collection->controllers as $controller) {
            if ($controller instanceof Controller) {
                $this->flushController($prefix, $controller, $routes);
            } elseif ($controller instanceof ControllerCollection) {
                $this->flushSubCollection($prefix, $controller, $routes);
            } else {
                throw new \LogicException('Controllers need to be Controller or ControllerCollection instances');
            }
        }

        $collection->controllers = array();

        return $routes;
    }

    /**
     * Add the Controller to the RouteCollection and freeze it
     *
     * @param string          $prefix
     * @param Controller      $controller
     * @param RouteCollection $routes
     */
    protected function flushController($prefix, Controller $controller, RouteCollection $routes)
    {
        // When mounting a controller class with a prefix most times you have a route with a blank path.
        // That is the only route that flushes to include an (unwanted) trailing slash.
        // This fixes that trailing slash.
        $controller->getRoute()->setPath(rtrim($prefix . $controller->getRoute()->getPath(), '/'));

        $this->generateControllerName($routes, $controller);
        $routes->add($controller->getRouteName(), $controller->getRoute());
        $controller->freeze();
    }

    /**
     * Add the ControllerCollection to the RouteCollection
     *
     * @param string               $prefix
     * @param ControllerCollection $collection
     * @param RouteCollection      $routes
     */
    protected function flushSubCollection($prefix, ControllerCollection $collection, RouteCollection $routes)
    {
        $prefix .= $this->normalizePrefix($collection->prefix);
        $collection->flushCollection($prefix, $collection, $routes);
    }

    /**
     * Same logic as the first part of {@see RouteCollection::addPrefix}
     *
     * @param $prefix
     *
     * @return string
     */
    protected function normalizePrefix($prefix)
    {
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
     */
    protected function generateControllerName(RouteCollection $routes, Controller $controller)
    {
        if (!$name = $controller->getRouteName()) {
            $name = $controller->generateRouteName('');
            while ($routes->get($name)) {
                $name .= '_';
            }
            $controller->bind($name);
        }
    }
}

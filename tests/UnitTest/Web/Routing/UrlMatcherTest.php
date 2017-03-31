<?php

namespace UnitTest\Web\Routing;

use GMO\Common\Web\Routing\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class UrlMatcherTest extends \PHPUnit_Framework_TestCase
{
    /** @var RouteCollection */
    protected $routes;
    /** @var UrlMatcher */
    protected $matcher;

    protected function setUp()
    {
        $this->routes = new RouteCollection();
        $this->matcher = new UrlMatcher($this->routes, new RequestContext());
    }

    public function testMatch()
    {
        $this->routes->add('foo', new Route('/foo'));
        $this->routes->add('bar', new Route('/bar/'));

        $this->assertMatch('foo', '/foo');
        $this->assertMatch('foo', '/foo/');
        $this->assertMatch('bar', '/bar');
        $this->assertMatch('bar', '/bar/');
    }

    public function testMatchFailure()
    {
        $this->setExpectedException(
            '\Symfony\Component\Routing\Exception\ResourceNotFoundException',
            'No routes found for "/derp".'
        );
        $this->matcher->match('/derp');

        $this->setExpectedException(
            '\Symfony\Component\Routing\Exception\ResourceNotFoundException',
            'No routes found for "/derp".'
        );
        $this->matcher->match('/derp/');
    }

    public function testTrailingSlashWithOptionalVariable()
    {
        $this->routes->add('foo', new Route('/foo/{bar}', array(), array('bar' => '\d*')));

        $this->assertMatch('foo', '/foo', array('bar' => ''));
        $this->assertMatch('foo', '/foo/', array('bar' => ''));
        $this->assertMatch('foo', '/foo/123', array('bar' => '123'));
    }

    public function testSchemeRequirement()
    {
        $this->routes->add('foo', new Route('/foo', array(), array(), array(), '', array('https')));
        $this->assertRedirect('https://localhost/foo', '/foo');
        $this->assertRedirect('https://localhost/foo', '/foo/');
    }

    public function testPrefixedRouteVariableOverridesBoundName()
    {
        $this->routes->add('asdf', new Route('/foo', array('_prefixed_route' => 'foo')));

        $this->assertMatch('foo', '/foo');
    }

    protected function assertMatch($route, $path, $variables = array())
    {
        $matched = $this->matcher->match($path);
        $this->assertInternalType('array', $matched);
        $this->assertEquals($route, $matched['_route']);
        unset($matched['_route']);
        $this->assertEquals($variables, $matched);
    }

    protected function assertRedirect($url, $path)
    {
        $matched = $this->matcher->match($path);
        $this->assertNull($matched['_route']);
        $this->assertEquals($url, $matched['url']);
    }
}

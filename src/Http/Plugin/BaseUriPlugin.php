<?php

namespace Gmo\Common\Http\Plugin;

use Http\Client\Common\Plugin;
use Http\Client\Common\Plugin\AddHostPlugin;
use Http\Discovery\UriFactoryDiscovery;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Add base uri to a request. Useful for base API URLs like http://domain.com/api.
 *
 * This uses Guzzle logic for base path concatenation. If the request path starts
 * with a "/" then the base path is not prepended.
 */
final class BaseUriPlugin implements Plugin
{
    /** @var AddHostPlugin */
    private $addHostPlugin;
    /** @var UriInterface|null */
    private $uri;

    /**
     * @param string|UriInterface $uri        Has to contain a host name and cans have a path.
     * @param array               $hostConfig Config for AddHostPlugin. @see AddHostPlugin::configureOptions
     */
    public function __construct($uri, array $hostConfig = [])
    {
        if (is_string($uri)) {
            $uri = UriFactoryDiscovery::find()->createUri($uri);
        }

        $this->addHostPlugin = new AddHostPlugin($uri, $hostConfig);

        if (rtrim($uri->getPath(), '/')) {
            if (substr($uri->getPath(), -1) === '/') {
                $uri = $uri->withPath(substr($uri->getPath(), 0, -1));
            }

            $this->uri = $uri;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $path = $request->getUri()->getPath();
        if ($this->uri && strpos($path, '/') !== 0) {
            if ($path) {
                $path = '/' . $path;
            }
            $path = $this->uri->getPath() . $path;

            $request = $request->withUri($request->getUri()->withPath($path));
        }

        return $this->addHostPlugin->handleRequest($request, $next, $first);
    }
}

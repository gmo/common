<?php

namespace Gmo\Common\Tests\Http\Plugin;

use Gmo\Common\Http\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\UriFactoryDiscovery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class BaseUriPluginTest extends TestCase
{
    public function testCreationWithUri()
    {
        $uri = UriFactoryDiscovery::find()->createUri('http://example.com/api');
        $plugin = new BaseUriPlugin($uri);

        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    public function testCreationWithString()
    {
        $plugin = new BaseUriPlugin('http://example.com/api');

        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    public function provideUris()
    {
        return [
            'relative path appends to base path' => [
                'http://example.com:8000/api',
                'foo',
                'http://example.com:8000/api/foo',
            ],
            'absolute path replaces base path' => [
                'http://example.com:8000/api',
                '/foo',
                'http://example.com:8000/foo',
            ],
            'empty path' => [
                'http://example.com:8000/api',
                '',
                'http://example.com:8000/api',
            ],
            'base path with trailing slash' => [
                'http://example.com:8000/api/',
                '',
                'http://example.com:8000/api',
            ],
            'empty base path' => [
                'http://example.com:8000',
                '/foo',
                'http://example.com:8000/foo',
            ],
            'base path is slash' => [
                'http://example.com:8000/',
                '/foo',
                'http://example.com:8000/foo',
            ],
        ];
    }

    /**
     * @dataProvider provideUris
     */
    public function testHandleRequest($baseUri, $requestUri, $resultUri)
    {
        $plugin = new BaseUriPlugin($baseUri);

        $request = MessageFactoryDiscovery::find()->createRequest('GET', $requestUri);

        /** @var RequestInterface $result */
        $result = $plugin->handleRequest($request, function ($request) { return $request; }, function () {});

        $this->assertEquals($resultUri, (string) $result->getUri());
    }
}

<?php

namespace GMO\Common\Web;

class RequestFactory
{
    protected $options = array();

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $options += array(
            'trust_proxies' => true,
        );
        $this->options = $options;
    }

    /**
     * Registers this as the factory to use when creating requests.
     *
     * @param array $options
     */
    public static function register($options = array())
    {
        $factory = new static($options);
        Request::setFactory(array($factory, 'createRequest'));
    }

    /**
     * Creates a new request with the given parameters.
     *
     * @param array           $query
     * @param array           $request
     * @param array           $attributes
     * @param array           $cookies
     * @param array           $files
     * @param array           $server
     * @param string|resource $content
     *
     * @return Request
     */
    public function createRequest(
        array $query = array(),
        array $request = array(),
        array $attributes = array(),
        array $cookies = array(),
        array $files = array(),
        array $server = array(),
        $content = null
    ) {
        $request = new Request($query, $request, $attributes, $cookies, $files, $server, $content);

        $proxies = $this->options['trust_proxies'];
        if ($proxies === true) {
            $proxies = array('127.0.0.1', $request->server->get('REMOTE_ADDR'));
        }
        if ($proxies === false) {
            Request::setTrustedProxies((array) $proxies);
        }

        return $request;
    }
}

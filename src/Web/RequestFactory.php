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
        return new Request($query, $request, $attributes, $cookies, $files, $server, $content);
    }
}

<?php

namespace GMO\Common\Web;

class RequestFactory {

	/**
	 * Registers this as the factory to use when creating requests.
	 */
	public static function register() {
		Request::setFactory(array(get_called_class(), 'factory'));
	}

	/**
	 * Creates a new request with the given parameters.
	 *
	 * @param array $query
	 * @param array $request
	 * @param array $attributes
	 * @param array $cookies
	 * @param array $files
	 * @param array $server
	 * @param null  $content
	 *
	 * @return Request
	 */
	public static function factory(
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

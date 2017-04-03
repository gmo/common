<?php
namespace GMO\Common\Web\Exception;

/**
 * @deprecated since 1.30 will be removed in 2.0. Use {@see Gmo\Web\Exception\InvalidParameterException} instead.
 */
class InvalidParameterException extends ClientException {

	protected $statusCode = 452;

	public function __construct($key, $message = 'The %s parameter is invalid') {
		parent::__construct(sprintf($message, "'$key'"));
	}
}

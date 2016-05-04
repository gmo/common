<?php
namespace Gmo\Common\Web\Exception;

class InvalidParameterException extends ClientException {

	protected $statusCode = 452;

	public function __construct($key, $message = 'The %s parameter is invalid') {
		parent::__construct(sprintf($message, "'$key'"));
	}
}

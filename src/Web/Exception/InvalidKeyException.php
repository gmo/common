<?php
namespace GMO\Common\Web\Exception;

class InvalidKeyException extends ClientException {

	protected $statusCode = 442;

	public function __construct($keyName = 'key', $message = 'The %s provided is invalid') {
		parent::__construct(sprintf($message, $keyName));
	}
}

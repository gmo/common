<?php
namespace GMO\Common\Web\Exception;

class NoKeyException extends ClientException {

	protected $statusCode = 441;

	public function __construct($keyName = 'key', $message = 'No %s was provided') {
		parent::__construct(sprintf($message, $keyName));
	}
}

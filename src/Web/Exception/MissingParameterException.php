<?php
namespace Gmo\Common\Web\Exception;

class MissingParameterException extends ClientException {

	protected $statusCode = 451;

	public function __construct($key, $message = 'The %s parameter is missing') {
		parent::__construct(sprintf($message, "'$key'"));
	}
}

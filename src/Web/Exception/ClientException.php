<?php
namespace Gmo\Common\Web\Exception;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ClientException extends \Exception implements HttpExceptionInterface {

	protected $statusCode = 400;
	protected $headers = array();

	public function getStatusCode() {
		return $this->statusCode;
	}

	public function getHeaders() {
		return $this->headers;
	}
}

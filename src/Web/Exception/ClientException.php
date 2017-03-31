<?php
namespace GMO\Common\Web\Exception;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * @deprecated since 1.30 will be removed in 2.0.
 */
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

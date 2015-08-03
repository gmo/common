<?php
namespace GMO\Common\Web\EventListener;

use GMO\Common\String;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Converts HTTP exceptions to JSON responses.
 */
class ExceptionToJsonListener implements EventSubscriberInterface {

	public function onKernelException(GetResponseForExceptionEvent $event) {
		if (!$this->isApplicable($event->getRequest())) {
			return;
		}

		$ex = $event->getException();
		$statusCode = 500;
		if ($ex instanceof HttpExceptionInterface) {
			$statusCode = $ex->getStatusCode();
		}

		$errorType = String::removeLast(String::className($ex), 'Exception');
		$response = new JsonResponse([
			'success'   => false,
			'errorType' => $errorType ?: 'Unknown',
			'code'      => $ex->getStatusCode(),
			'message'   => $ex->getMessage(),
		]);
		$response->setStatusCode($statusCode, $errorType ?: null);
		$event->setResponse($response);
	}

	protected function isApplicable(Request $request) {
		return String::startsWith($request->getPathInfo(), '/api/', false);
	}

	public static function getSubscribedEvents() {
		return [
			KernelEvents::EXCEPTION => ['onKernelException', -7],
		];
	}
}

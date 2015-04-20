<?php
namespace Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Log request, response and exceptions.
 * Adapted from {@see Silex\EventListener\LogListener}
 */
class HttpLogListener implements EventSubscriberInterface {

	/**
	 * Logs master requests on event KernelEvents::REQUEST
	 *
	 * @param GetResponseEvent $event
	 */
	public function onKernelRequest(GetResponseEvent $event) {
		if (!$event->isMasterRequest()) {
			return;
		}
		$this->logger->info('Received request');
	}

	/**
	 * Logs master response on event KernelEvents::RESPONSE
	 *
	 * @param FilterResponseEvent $event
	 */
	public function onKernelResponse(FilterResponseEvent $event) {
		if (!$event->isMasterRequest()) {
			return;
		}
		$response = $event->getResponse();

		if ($response instanceof RedirectResponse) {
			$this->logger->info('Sending redirect', [
				'target' => $response->getTargetUrl(),
				'code' => $response->getStatusCode(),
			]);
		} else {
			$this->logger->info('Sending response', [
				'code' => $response->getStatusCode(),
			]);
		}
	}

	/**
	 * Logs uncaught exceptions on event KernelEvents::EXCEPTION
	 *
	 * @param GetResponseForExceptionEvent $event
	 */
	public function onKernelException(GetResponseForExceptionEvent $event) {
		$e = $event->getException();
		if ($e instanceof NotFoundHttpException) {
			$this->logger->warning('No route found');
			return;
		}
		if ($e instanceof MethodNotAllowedHttpException) {
			/** @var \Symfony\Component\Routing\Exception\MethodNotAllowedException $previous */
			$previous = $e->getPrevious();
			$this->logger->warning('Method not allowed', [
				'allowed' => $previous->getAllowedMethods(),
			]);
			return;
		}

		$message = $e->getMessage();
		$level = LogLevel::ERROR;
		$context = [
			'exception' => $e,
		];
		if ($e instanceof HttpExceptionInterface) {
			$code = $e->getStatusCode();
			$context['statusCode'] = $code;
			if ($code < 500) {
				$level = LogLevel::WARNING;
			}
		}

		$this->logger->log($level, $message, $context);
	}

	public static function getSubscribedEvents() {
		return [
			KernelEvents::REQUEST => ['onKernelRequest', 0],
			KernelEvents::RESPONSE => ['onKernelResponse', 0],
			/*
			 * Priority -4 is used to come after those from SecurityServiceProvider (0)
			 * but before the error handlers added with Silex\Application::error (defaults to -8)
			 */
			KernelEvents::EXCEPTION => ['onKernelException', -4],
		];
	}

	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	public function __construct(LoggerInterface $logger) {
		$this->setLogger($logger);
	}

	/** @var LoggerInterface */
	protected $logger;
}

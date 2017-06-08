<?php

namespace GMO\Common\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
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
 *
 * @deprecated since 1.30 will be removed in 2.0. Use {@see Gmo\Web\EventListener\HttpLogListener} instead.
 */
class HttpLogListener implements EventSubscriberInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs master requests on event KernelEvents::REQUEST
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
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
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $response = $event->getResponse();

        if ($response instanceof RedirectResponse) {
            $this->logger->info('Sending redirect', array(
                'target' => $response->getTargetUrl(),
                'code'   => $response->getStatusCode(),
            ));
        } else {
            $this->logger->debug('Sending response', array(
                'code' => $response->getStatusCode(),
            ));
        }
    }

    /**
     * Logs uncaught exceptions on event KernelEvents::EXCEPTION
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();
        if ($e instanceof NotFoundHttpException) {
            $this->logger->warning('No route found');

            return;
        }
        if ($e instanceof MethodNotAllowedHttpException) {
            /** @var \Symfony\Component\Routing\Exception\MethodNotAllowedException $previous */
            $previous = $e->getPrevious();
            $this->logger->warning('Method not allowed', array(
                'allowed' => $previous->getAllowedMethods(),
            ));

            return;
        }

        $message = $e->getMessage();
        $level = LogLevel::CRITICAL;
        $context = array(
            'exception' => $e,
        );
        if ($e instanceof HttpExceptionInterface) {
            $code = $e->getStatusCode();
            $context['statusCode'] = $code;
            if ($code < 500) {
                $level = LogLevel::WARNING;
            }
        }

        $this->logger->log($level, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST   => array('onKernelRequest', 0),
            KernelEvents::RESPONSE  => array('onKernelResponse', 0),
            /*
             * Priority -4 is used to come after those from SecurityServiceProvider (0)
             * but before the error handlers added with Silex\Application::error (defaults to -8)
             */
            KernelEvents::EXCEPTION => array('onKernelException', -4),
        );
    }
}

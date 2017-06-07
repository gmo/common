<?php

namespace GMO\Common\Web\EventListener;

use GMO\Common\Str;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Converts HTTP exceptions to JSON responses.
 *
 * @deprecated since 1.30 will be removed in 2.0. Use {@see Gmo\Web\EventListener\ExceptionToJsonListener} instead.
 */
class ExceptionToJsonListener implements EventSubscriberInterface
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$this->isApplicable($event->getRequest())) {
            return;
        }

        $ex = $event->getException();
        $statusCode = 500;
        if ($ex instanceof HttpExceptionInterface) {
            $statusCode = $ex->getStatusCode();
        }

        $errorType = Str::removeLast(Str::className($ex), 'Exception');
        $response = new JsonResponse(array(
            'success'   => false,
            'errorType' => $errorType ?: 'Unknown',
            'code'      => $statusCode,
            'message'   => $ex->getMessage(),
        ));
        $response->setStatusCode($statusCode, $errorType ?: null);
        $event->setResponse($response);
    }

    protected function isApplicable(Request $request)
    {
        return Str::startsWith($request->getPathInfo(), '/api/', false);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', -7),
        );
    }
}

<?php
namespace GMO\Common\Web\Routing;

use Psr\Log\LoggerInterface;
use Silex\Application;
use Silex\LazyUrlMatcher;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\EventListener\RouterListener as RouterListenerBase;

/**
 * This wraps the original RouterListener and replaces the logging
 */
class RouterListener implements ServiceProviderInterface, EventSubscriberInterface {

	public function onKernelFinishRequest(FinishRequestEvent $event) {
		$this->wrappedRouter->onKernelFinishRequest($event);
	}

	public function onKernelRequest(GetResponseEvent $event) {
		$request = $event->getRequest();
		$this->wrappedRouter->onKernelRequest($event);

		$this->logRoute($request->attributes);
	}

	protected function logRoute(ParameterBag $attributes) {
		$this->logger->debug('Matched route', [
			'route' => $attributes->get('_route'),
			'parameters' => $attributes->get('_route_params'),
		]);
	}

	/**
	 * @deprecated Deprecated since version 2.4, to be moved to a private function in 3.0.
	 * @param Request $request
	 */
	public function setRequest(Request $request = null) {
		$this->wrappedRouter->setRequest($request);
	}

	public function register(Application $app) {
		$app['dispatcher'] = $app->share($app->extend('dispatcher', function (EventDispatcher $dispatcher, $app) {
			$this->removeOldListener($dispatcher);
			$this->addNewListener($dispatcher, $app);
			return $dispatcher;
		}));
	}

	/**
	 * Removes old listener from dispatcher
	 *
	 * @param EventDispatcher $dispatcher
	 */
	private function removeOldListener(EventDispatcher $dispatcher) {
		$listeners = $dispatcher->getListeners(KernelEvents::REQUEST);
		foreach ($listeners as $listener) {
			if (is_array($listener) && $listener[0] instanceof RouterListenerBase) {
				$dispatcher->removeSubscriber($listener[0]);
			}
		}
	}

	/**
	 * Adds new listener to dispatcher
	 *
	 * @param EventDispatcher $dispatcher
	 * @param Application     $app
	 */
	private function addNewListener(EventDispatcher $dispatcher, Application $app) {
		$urlMatcher = new LazyUrlMatcher(function () use ($app) {
			return $app['url_matcher'];
		});
		$this->wrappedRouter = new RouterListenerBase($urlMatcher, $app['request_context'], null, $app['request_stack']);
		$dispatcher->addSubscriber($this);
	}

	public function boot(Application $app) {
		$this->setLogger($app['logger']);
	}

	public static function getSubscribedEvents() {
		return [
			KernelEvents::REQUEST => ['onKernelRequest', 32],
			KernelEvents::FINISH_REQUEST => ['onKernelFinishRequest', 0],
		];
	}

	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	/** @var RouterListenerBase */
	protected $wrappedRouter;
	/** @var LoggerInterface */
	protected $logger;
}

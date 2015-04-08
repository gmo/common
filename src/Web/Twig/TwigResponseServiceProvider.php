<?php
namespace Gmo\Common\Web\Twig;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TwigResponseServiceProvider implements ServiceProviderInterface, EventSubscriberInterface {

	public function onResponse(FilterResponseEvent $event) {
		$response = $event->getResponse();
		if (!$response instanceof TwigResponse) {
			return;
		}
		/** @var \Twig_Environment $twig */
		$twig = $this->app['twig'];
		$content = $twig->render($response->getTemplate(), $response->getVariables()->toArray());
		$newResponse = new Response($content, $response->getStatusCode());
		$newResponse->headers = $response->headers;
		$event->setResponse($newResponse);
	}

	public function register(Application $app) {
		$this->app = $app;
	}

	public function boot(Application $app) {
		$app['dispatcher']->addSubscriber($this);
	}

	/** @inheritdoc */
	public static function getSubscribedEvents() {
		return array(
			KernelEvents::RESPONSE => array('onResponse', -100),
		);
	}

	/** @var Application */
	protected $app;
}

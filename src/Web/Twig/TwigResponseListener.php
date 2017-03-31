<?php
namespace GMO\Common\Web\Twig;

use Silex\Application;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @deprecated since 1.30 will be removed in 2.0. Use {@see Gmo\Web\EventListener\TemplateViewListener} instead.
 */
class TwigResponseListener implements EventSubscriberInterface {

	public function onResponse(FilterResponseEvent $event) {
		$response = $event->getResponse();
		if (!$response instanceof RenderableInterface || $response->isRendered()) {
			return;
		}
		$response->render($this->app['twig']);
	}

	public static function getSubscribedEvents() {
		return array(
			KernelEvents::RESPONSE => array('onResponse', -100),
		);
	}

	public function __construct(Application $app) {
		$this->app = $app;
	}

	protected $app;
}

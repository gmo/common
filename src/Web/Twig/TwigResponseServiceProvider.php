<?php
namespace GMO\Common\Web\Twig;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TwigResponseServiceProvider implements ServiceProviderInterface {

	public function onResponse(Request $request, Response $response) {
		if ($response instanceof TwigResponse) {
			$response->render($this->app['twig']);
		}
	}

	public function register(Application $app) {
		$this->app = $app;
		$app->after(array($this, 'onResponse'), Application::LATE_EVENT);
	}

	public function boot(Application $app) { }

	/** @var Application */
	protected $app;
}

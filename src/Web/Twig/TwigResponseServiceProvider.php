<?php
namespace Gmo\Common\Web\Twig;

use Silex\Application;
use Silex\ServiceProviderInterface;

class TwigResponseServiceProvider implements ServiceProviderInterface {

	public function register(Application $app) { }

	public function boot(Application $app) {
		$app['dispatcher']->addSubscriber(new TwigResponseListener($app));
	}
}

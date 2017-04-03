<?php
namespace Gmo\Common\Session;

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @deprecated since 1.30 will be removed in 2.0.
 */
class JwtCookieSessionServiceProvider extends SessionServiceProvider {

	public function register(Application $app) {
		parent::register($app);

		$app['session.storage'] = $app->share(function($app) {
			return new JwtCookieSessionStorage(
				$app['session.storage.cookie.name'],
				$app['session.storage.cookie.secret']
			);
		});

		$app['session.bag.attribute'] = function($app) {
			return new AutoSavingAttributeBag($app['session.storage']);
		};

		$app['session'] = $app->share(function($app) {
			return new Session($app['session.storage'], $app['session.bag.attribute']);
		});
	}
}

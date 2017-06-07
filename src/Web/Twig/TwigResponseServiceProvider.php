<?php

namespace GMO\Common\Web\Twig;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * @deprecated since 1.30 will be removed in 2.0. Use {@see Gmo\Web\Provider\TemplateViewServiceProvider} instead.
 */
class TwigResponseServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber(new TwigResponseListener($app));
    }
}

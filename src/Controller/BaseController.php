<?php
namespace GMO\Common\Web\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BaseController implements ControllerProviderInterface {

	public function connect(Application $app) {
		$this->app = $app;
	}

	/**
	 * Shortcut for {@see UrlGeneratorInterface::generate}
	 * @param string $name The name of the route
	 * @param array  $params An array of parameters
	 * @param bool   $referenceType The type of reference to be generated (one of the constants)
	 * @return string
	 */
	public function generateUrl($name, $params = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH) {
		/** @var UrlGeneratorInterface $generator */
		$generator = $this->app['url_generator'];
		return $generator->generate($name, $params, $referenceType);
	}

	/**
	 * Redirects the user to another URL.
	 *
	 * @param string $url    The URL to redirect to
	 * @param int    $status The status code (302 by default)
	 *
	 * @return RedirectResponse
	 */
	public function redirect($url, $status = 302)
	{
		return new RedirectResponse($url, $status);
	}

	/**
	 * Returns a RedirectResponse to the given route with the given parameters.
	 *
	 * @param string $route      The name of the route
	 * @param array  $parameters An array of parameters
	 * @param int    $status     The status code to use for the Response
	 *
	 * @return RedirectResponse
	 */
	public function redirectToRoute($route, array $parameters = array(), $status = 302)
	{
		return $this->redirect($this->generateUrl($route, $parameters), $status);
	}

	/**
	 * @return Session
	 */
	public function getSession() {
		return $this->app['session'];
	}

	/**
	 * Convert some data into a JSON response.
	 *
	 * @param mixed $data    The response data
	 * @param int   $status  The response status code
	 * @param array $headers An array of response headers
	 *
	 * @return JsonResponse
	 */
	public function json($data = array(), $status = 200, array $headers = array()) {
		return new JsonResponse($data, $status, $headers);
	}

	/** @var Application */
	protected $app;
}

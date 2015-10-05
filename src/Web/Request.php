<?php
namespace GMO\Common\Web;

use Symfony\Component\HttpFoundation\Request as RequestBase;

/**
 * {@inheritdoc}
 *
 * Sub-classing to use our ParameterBag.
 */
class Request extends RequestBase {

	/**
	 * Query string parameters ($_GET).
	 *
	 * @var ParameterBag
	 */
	public $query;

	/**
	 * Request body parameters ($_POST).
	 *
	 * @var ParameterBag
	 */
	public $request;

	/**
	 * {@inheritdoc}
	 */
	public function initialize(
		array $query = array(),
		array $request = array(),
		array $attributes = array(),
		array $cookies = array(),
		array $files = array(),
		array $server = array(),
		$content = null
	) {
		parent::initialize($query, $request, $attributes, $cookies, $files, $server, $content);

		$this->request = new ParameterBag($request);
		$this->query = new ParameterBag($query);
	}

	/**
	 * {@inheritdoc}
	 */
	public static function createFromGlobals() {
		$request = parent::createFromGlobals();

		$request->request = new ParameterBag($request->request->all());

		return $request;
	}
}

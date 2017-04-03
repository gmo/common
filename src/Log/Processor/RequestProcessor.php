<?php
namespace Gmo\Common\Log\Processor;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @deprecated since 1.30 will be removed in 2.0. Use {@see Gmo\Web\Logger\Processor\RequestProcessor} instead.
 */
class RequestProcessor {

	public function __invoke(array $record) {
		if (!$request = $this->requestStack->getCurrentRequest()) {
			return $record;
		}

		$params = [
			'method' => $request->getMethod(),
			'host' => $request->getHost(),
			'path' =>  $request->getPathInfo(),
			'query' => $request->query->all(),
			'userAgent' => $request->server->get('HTTP_USER_AGENT'),
		];
		if ($request->isMethod('POST')) {
			$params['body'] = $request->request->all();
		}
		if ($referer = $request->headers->get('referer')) {
			$params['referer'] = $referer;
		}

		$record['extra']['request'] = $params;
		return $record;
	}

	public function __construct(RequestStack $requestStack) {
		$this->requestStack = $requestStack;
	}

	protected $requestStack;
}

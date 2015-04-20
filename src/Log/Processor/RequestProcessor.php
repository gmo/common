<?php
namespace GMO\Common\Log\Processor;

use Symfony\Component\HttpFoundation\RequestStack;

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

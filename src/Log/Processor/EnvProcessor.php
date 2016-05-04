<?php
namespace Gmo\Common\Log\Processor;

class EnvProcessor {

	public function __invoke(array $record) {
		$record['extra']['env'] = $this->env;
		return $record;
	}

	public function __construct($env) {
		$this->env = $env;
	}

	protected $env;
}

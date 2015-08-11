<?php
namespace GMO\Common\Log\Handler;

use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\RedisHandler;
use Monolog\Logger;

class LogstashHandler extends RedisHandler {

	/**
	 * @param string $contextPrefix
	 */
	public function setContextPrefix($contextPrefix) {
		if ($this->frozen) {
			throw new \LogicException('Context prefix cannot be set after formatter has been created');
		}
		$this->contextPrefix = $contextPrefix;
	}

	/**
	 * @param string $extraPrefix
	 */
	public function setExtraPrefix($extraPrefix) {
		if ($this->frozen) {
			throw new \LogicException('Extra prefix cannot be set after formatter has been created');
		}
		$this->extraPrefix = $extraPrefix;
	}

	protected function getDefaultFormatter() {
		$this->frozen = true;
		return new LogstashFormatter($this->appName, null, $this->extraPrefix, $this->contextPrefix, LogstashFormatter::V1);
	}

	/**
	 * @param \GMO\Cache\Redis|\Predis\ClientInterface|\Redis $redis
	 * @param string                                          $appName
	 * @param string                                          $key
	 * @param int                                             $level
	 * @param bool                                            $bubble
	 */
	public function __construct($redis, $appName, $key = 'logging', $level = Logger::DEBUG, $bubble = true) {
		$this->appName = $appName;
		if ($redis instanceof \GMO\Cache\Redis) {
			$redis = $redis->redis;
		}
		parent::__construct($redis, $key, $level, $bubble);
	}

	protected $appName;
	protected $frozen = false;
	protected $contextPrefix = 'ctxt.';
	protected $extraPrefix = null;
}

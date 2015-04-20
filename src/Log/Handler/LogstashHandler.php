<?php
namespace GMO\Common\Log\Handler;

use GMO\Cache\Redis;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\RedisHandler;
use Monolog\Logger;

class LogstashHandler extends RedisHandler {

	protected function getDefaultFormatter() {
		return new LogstashFormatter($this->appName, null, null, 'ctxt.', LogstashFormatter::V1);
	}

	/**
	 * @param Redis    $redis
	 * @param string   $appName
	 * @param string   $key
	 * @param int      $level
	 * @param bool     $bubble
	 */
	public function __construct($redis, $appName, $key, $level = Logger::DEBUG, $bubble = true) {
		$this->appName = $appName;
		parent::__construct($redis->redis, $key, $level, $bubble);
	}

	protected $appName;
}

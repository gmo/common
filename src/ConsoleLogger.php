<?php
namespace Gmo\Common;

use Psr\Log\AbstractLogger;

class ConsoleLogger extends AbstractLogger {

	/**
	 * Logs with an arbitrary level.
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 * @return null
	 */
	public function log($level, $message, array $context = array()) {
		echo sprintf("%s %s: %s\n", date("Y-m-d H:i:s"), strtoupper($level), $message);
	}
}

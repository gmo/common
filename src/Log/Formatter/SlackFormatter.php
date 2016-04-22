<?php
namespace GMO\Common\Log\Formatter;

use Monolog\Formatter\NormalizerFormatter;

/**
 * Does some of the formatting for SlackHandler, mostly just normalization
 */
class SlackFormatter extends NormalizerFormatter {

	/**
	 * @param \Exception|\Throwable $e
	 *
	 * @return string
	 */
	protected function normalizeException($e) {
		$msg = $e->getMessage();
		return get_class($e) . (!empty($msg) ? ': ' . $msg : '');
	}
}

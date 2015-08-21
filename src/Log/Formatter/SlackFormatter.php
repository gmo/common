<?php
namespace GMO\Common\Log\Formatter;

use Exception;
use Monolog\Formatter\NormalizerFormatter;

/**
 * Does some of the formatting for SlackHandler, mostly just normalization
 */
class SlackFormatter extends NormalizerFormatter {

	/**
	 * {@inheritdoc}
	 */
	protected function normalizeException(Exception $e) {
		$msg = $e->getMessage();
		return get_class($e) . (!empty($msg) ? ': ' . $msg : '');
	}
}

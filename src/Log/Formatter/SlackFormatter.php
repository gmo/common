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
		$data = get_class($e);
		if (!empty($e->getMessage())) {
			$data .= ': ' . $e->getMessage();
		}
		return $data;
	}
}

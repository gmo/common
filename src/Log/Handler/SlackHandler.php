<?php
namespace GMO\Common\Log\Handler;

use GMO\Common\Log\Formatter\SlackFormatter;
use GMO\Common\String;
use Monolog\Handler\SlackHandler as SlackHandlerBase;
use Monolog\Logger;

/**
 * {@inheritdoc}
 *
 * Subclassing to tweak formatting and change defaults
 */
class SlackHandler extends SlackHandlerBase {

	/**
	 * {@inheritdoc}
	 */
	public function __construct(
		$token,
		$channel,
		$username = 'Logger',
		$useAttachment = true,
		$iconEmoji = 'page_with_curl',
		$level = Logger::CRITICAL,
		$bubble = true,
		$useShortAttachment = false,
		$includeContextAndExtra = true
	) {
		parent::__construct(
			$token,
			$channel,
			$username,
			$useAttachment,
			$iconEmoji,
			$level,
			$bubble,
			$useShortAttachment,
			$includeContextAndExtra
		);
	}

	/**
	 * {@inheritdoc}
	 *
	 * Using SlackFormatter
	 */
	protected function getDefaultFormatter() {
		return new SlackFormatter();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function write(array $record) {
		// Use actual formatted data instead of unformatted record
		$record = $record['formatted'];
		parent::write($record);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function prepareContentData($record) {
		// Flatten sub-arrays, otherwise data will be lost (only first value of array will be used)
		foreach ($record['context'] as $key => $value) {
			if (!is_scalar($value)) {
				$record['context'][$key] = $this->stringify($value);
			}
		}
		foreach ($record['extra'] as $key => $value) {
			if (!is_scalar($value)) {
				$record['extra'][$key] = $this->stringify($value);
			}
		}

		$data = parent::prepareContentData($record);

		$data['attachments'] = json_decode($data['attachments'], true);

		// Set short property for the fields it applies too.
		// This tells slack to put multiple on one row.
		$shortLength = 40;
		foreach ($data['attachments'][0]['fields'] as $index => &$field) {
			if ($index === 0) {
				continue; // Never make Message short
			}
			if (mb_strlen($field['title']) < $shortLength && mb_strlen($field['value']) < $shortLength) {
				$field['short'] = true;
			}

			$field['title'] = String::humanize($field['title']);
		}

		$data['attachments'] = json_encode($data['attachments']);
		return $data;
	}
}

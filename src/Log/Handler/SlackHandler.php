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
	 * @param string      $token                  Slack API token
	 * @param string      $channel                Slack channel/user (encoded ID or name)
	 * @param string      $username               Name of a bot
	 * @param int         $level                  The minimum logging level at which this handler will be triggered
	 * @param bool        $bubble                 Whether the messages that are handled can bubble up the stack or not
	 * @param bool        $includeContextAndExtra Whether the attachment should include context and extra data
	 * @param string|null $iconEmoji              The emoji name to use (or null)
	 * @param bool        $useAttachment          Whether the message should be added to Slack as attachment (plain text otherwise)
	 * @param bool        $useShortAttachment     Whether the the context/extra messages added to Slack as attachments are in a short style
	 */
	public function __construct(
		$token,
		$channel,
		$username = 'Logger',
		$level = Logger::CRITICAL,
		$bubble = true,
		$includeContextAndExtra = true,
		$iconEmoji = 'page_with_curl',
		$useAttachment = true,
		$useShortAttachment = false
	) {
		parent::__construct(
			$token,
			$channel,
			$username,
			$useAttachment,
			$iconEmoji,
			$level ?: Logger::CRITICAL,
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

		$fields = &$data['attachments'][0]['fields'];

		// Add channel to fields
		array_splice($fields, 2, 0, array(
			array(
				'title' => 'Channel',
				'value' => $record['channel'],
				'short' => false,
			)
		));

		// Set short property for the fields it applies too.
		// This tells slack to put multiple on one row.
		$shortLength = 40;
		foreach ($fields as $index => &$field) {
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

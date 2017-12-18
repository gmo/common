<?php

namespace GMO\Common\Log\Handler;

use Gmo\Common\Deprecated;
use GMO\Common\Json;
use GMO\Common\Log\Formatter\SlackFormatter;
use GMO\Common\Str;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SlackHandler as SlackHandlerBase;
use Monolog\Logger;

Deprecated::cls('\GMO\Common\Log\Handler\SlackHandler', 1.32, '\Gmo\Web\Logger\Handler\SlackHandler');

/**
 * {@inheritdoc}
 *
 * Subclassing to tweak formatting and change defaults
 *
 * @deprecated since 1.32 and will be removed in 2.0. Use {@see \Gmo\Web\Logger\Handler\SlackHandler} instead.
 */
class SlackHandler extends SlackHandlerBase
{
    /** @var LineFormatter */
    private $lineFormatter;

    /**
     * @param string      $token                  Slack API token
     * @param string      $channel                Slack channel/user (encoded ID or name)
     * @param string      $username               Name of a bot
     * @param int         $level                  The minimum logging level at which this handler will be triggered
     * @param bool        $bubble                 Whether the messages that are handled can bubble up the stack or not
     * @param bool        $includeContextAndExtra Whether the attachment should include context and extra data
     * @param string|null $iconEmoji              The emoji name to use (or null)
     * @param bool        $useAttachment          Whether the message should be added to Slack as attachment (plain
     *                                            text otherwise)
     * @param bool        $useShortAttachment     Whether the the context/extra messages added to Slack as attachments
     *                                            are in a short style
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
        $this->lineFormatter = new LineFormatter();
    }

    /**
     * {@inheritdoc}
     *
     * Using SlackFormatter
     */
    protected function getDefaultFormatter()
    {
        return new SlackFormatter();
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        // Use actual formatted data instead of unformatted record
        // Except for `datetime` as parent expects that to be a DateTime object
        $record['formatted']['datetime'] = $record['datetime'];
        $record = $record['formatted'];
        parent::write($record);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContentData($record)
    {
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

        $data['attachments'] = Json::parse($data['attachments']);

        $fields = &$data['attachments'][0]['fields'];

        // Add channel to fields
        array_splice(
            $fields,
            2,
            0,
            array(
                array(
                    'title' => 'Channel',
                    'value' => $record['channel'],
                    'short' => false,
                ),
            )
        );

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

            $field['title'] = Str::humanize($field['title']);
        }

        $data['attachments'] = Json::dump($data['attachments']);

        return $data;
    }

    /**
     * Stringifies an array of key/value pairs to be used in attachment fields
     *
     * @param  array $fields
     *
     * @return string
     */
    protected function stringify($fields)
    {
        $string = '';
        foreach ($fields as $var => $val) {
            $string .= $var . ': ' . $this->lineFormatter->stringify($val) . " | ";
        }

        $string = rtrim($string, " |");

        return $string;
    }
}

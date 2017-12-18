<?php

namespace GMO\Common\Log\Formatter;

use Gmo\Common\Deprecated;
use Monolog\Formatter\LogstashFormatter as BaseLogstashFormatter;

Deprecated::cls('\GMO\Common\Log\Formatter\LogstashFormatter', 1.32, '\Gmo\Web\Logger\Formatter\LogstashFormatter');

/**
 * @deprecated since 1.32 and will be removed in 2.0. Use {@see \Gmo\Web\Logger\Formatter\LogstashFormatter} instead.
 */
class LogstashFormatter extends BaseLogstashFormatter
{
    /**
     * {@inheritdoc}
     */
    protected function formatV1(array $record)
    {
        $context = isset($record['context']) ? $record['context'] : array();
        unset($record['context']);
        $message = parent::formatV1($record);

        if (!empty($context)) {
            $message[$this->contextPrefix] = $context;
        }

        return $message;
    }
}

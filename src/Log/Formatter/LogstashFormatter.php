<?php

namespace Gmo\Common\Log\Formatter;

use Monolog\Formatter\LogstashFormatter as BaseLogstashFormatter;

class LogstashFormatter extends BaseLogstashFormatter
{
    /**
     * {@inheritdoc}
     */
    protected function formatV1(array $record)
    {
        $context = isset($record['context']) ? $record['context'] : [];
        unset($record['context']);
        $message = parent::formatV1($record);

        if (!empty($context)) {
            $message[$this->contextPrefix] = $context;
        }

        return $message;
    }
}

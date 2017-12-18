<?php

namespace GMO\Common\Log\Formatter;

use Gmo\Common\Deprecated;
use Monolog\Formatter\NormalizerFormatter;

Deprecated::cls('\GMO\Common\Log\Formatter\SlackFormatter', 1.32, '\Gmo\Web\Logger\Formatter\SlackFormatter');

/**
 * Does some of the formatting for SlackHandler, mostly just normalization
 *
 * @deprecated since 1.32 and will be removed in 2.0. Use {@see \Gmo\Web\Logger\Formatter\SlackFormatter} instead.
 */
class SlackFormatter extends NormalizerFormatter
{
    /**
     * @param \Exception|\Throwable $e
     *
     * @return string
     */
    protected function normalizeException($e)
    {
        $msg = $e->getMessage();

        return get_class($e) . (!empty($msg) ? ': ' . $msg : '');
    }
}

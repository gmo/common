<?php

namespace Gmo\Common\Log;

use Gmo\Common\Deprecated;
use Psr\Log\AbstractLogger;

Deprecated::cls('Gmo\Common\Log\ConsoleLogger', 1.32);

/**
 * @deprecated since 1.32 and will be removed in 2.0.
 */
class ConsoleLogger extends AbstractLogger
{
    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        echo sprintf("%s %s: %s\n", date("Y-m-d H:i:s"), strtoupper($level), $message);
    }
}

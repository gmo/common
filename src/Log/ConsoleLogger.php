<?php

namespace Gmo\Common\Log;

use Psr\Log\AbstractLogger;

class ConsoleLogger extends AbstractLogger
{
    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        echo sprintf("%s %s: %s\n", date("Y-m-d H:i:s"), strtoupper($level), $message);
    }
}

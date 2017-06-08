<?php

namespace Gmo\Common\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Delegates logging to a given logger.
 * Allows a real logger to be swapped in after this logger is given to an object.
 */
class DelegateLogger extends AbstractLogger implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}

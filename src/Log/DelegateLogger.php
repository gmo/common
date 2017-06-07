<?php

namespace GMO\Common\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Delegates logging to a given logger.
 * Allows a real logger to be swapped in after this logger is given to an object.
 */
class DelegateLogger extends AbstractLogger implements LoggerAwareInterface
{
    /** @var LoggerInterface|null */
    protected $logger;

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
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

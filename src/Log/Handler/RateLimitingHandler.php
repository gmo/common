<?php

namespace GMO\Common\Log;

use Monolog\Handler\HandlerInterface;
use Predis;

/**
 * This wraps a handler and limits the rate at which records can be handled.
 *
 * Records are identified by message.
 */
class RateLimitingHandler extends AbstractDecoratorHandler
{
    /** @var Predis\ClientInterface */
    protected $redis;
    /** @var HandlerInterface */
    protected $handler;
    /** @var int seconds */
    protected $rateLimit;

    /**
     * Constructor.
     *
     * @param Predis\ClientInterface $redis     The predis client used to store rate limited messages.
     * @param HandlerInterface       $handler   The handler to rate limit.
     * @param int                    $rateLimit Number of seconds a certain message can be handled.
     *                                          Zero or a negative number disables rate limiting.
     */
    public function __construct(Predis\ClientInterface $redis, HandlerInterface $handler, $rateLimit = 1)
    {
        $this->redis = $redis;
        $this->handler = $handler;
        $this->rateLimit = $rateLimit;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if (!$this->handler->isHandling($record) || $this->isLimited($record)) {
            return false;
        }

        return $this->handler->handle($record);
    }

    /**
     * Returns whether a record as been limited or not.
     *
     * @param array $record
     *
     * @return bool
     */
    protected function isLimited(array $record)
    {
        if ($this->rateLimit <= 0) {
            return false;
        }

        $key = 'logger:lock:' . $this->identifyRecord($record);

        return !(bool) $this->redis->set($key, '', 'EX', $this->rateLimit, 'NX');
    }

    /**
     * Returns a string identifier to base rate limits on.
     *
     * @param array $record
     *
     * @return string
     */
    protected function identifyRecord(array $record)
    {
        return md5($record['message']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getHandler()
    {
        return $this->handler;
    }
}

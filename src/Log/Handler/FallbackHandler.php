<?php

namespace Gmo\Common\Log\Handler;

use Monolog\Handler\HandlerInterface;

/**
 * Wraps a main handler and fallback decorator. If the main handler throws an exception that's an instance of the
 * exceptions given, the record(s) is given to the fallback handler instead.
 */
class FallbackHandler extends AbstractDecoratorHandler
{
    /** @var HandlerInterface */
    protected $main;
    /** @var HandlerInterface */
    protected $fallback;
    /** @var string[] */
    protected $exceptionClasses;

    /**
     * Constructor.
     *
     * @param HandlerInterface $main
     * @param HandlerInterface $fallback
     * @param string[]         $exceptionClasses
     */
    public function __construct(HandlerInterface $main, HandlerInterface $fallback, array $exceptionClasses = [])
    {
        $this->main = $main;
        $this->fallback = $fallback;
        $this->exceptionClasses = $exceptionClasses;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        try {
            return parent::handle($record);
        } catch (\Exception $e) {
            if (!$this->shouldCatch($e)) {
                throw $e;
            }
        }
        $this->fallback->handle($record);

        try {
            return $this->getBubble() === false;
        } catch (\BadMethodCallException $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        try {
            parent::handleBatch($records);
        } catch (\Exception $e) {
            if (!$this->shouldCatch($e)) {
                throw $e;
            }
        }

        $this->fallback->handleBatch($records);
    }

    /**
     * For decorator methods.
     *
     * @return HandlerInterface
     */
    protected function getHandler()
    {
        return $this->main;
    }

    /**
     * Returns true if the exception is an instance of the exceptions we are catching.
     *
     * @param \Exception $e
     *
     * @return bool
     */
    protected function shouldCatch(\Exception $e)
    {
        foreach ($this->exceptionClasses as $exceptionClass) {
            if (is_a($e, $exceptionClass, true)) {
                return true;
            }
        }

        return false;
    }
}

<?php

namespace GMO\Common\Log;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;

/**
 * Base for decorating Monolog handlers
 */
abstract class AbstractDecoratorHandler implements HandlerInterface
{
    /**
     * @return HandlerInterface
     */
    abstract protected function getHandler();

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        return $this->getHandler()->isHandling($record);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        return $this->getHandler()->handle($record);
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        $this->getHandler()->handleBatch($records);
    }

    /**
     * {@inheritdoc}
     */
    public function pushProcessor($callback)
    {
        $this->getHandler()->pushProcessor($callback);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function popProcessor()
    {
        return $this->getHandler()->popProcessor();
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->getHandler()->setFormatter($formatter);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter()
    {
        return $this->getHandler()->getFormatter();
    }

    /**
     * Magic method to forward all unknown calls to original handler.
     *
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($name, $args)
    {
        if (!method_exists($this->getHandler(), $name)) {
            throw new \BadMethodCallException('Handler does not have that method.');
        }

        return call_user_func_array(array($this->getHandler(), 'name'), $args);
    }
}

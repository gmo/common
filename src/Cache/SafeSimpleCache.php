<?php

namespace Gmo\Common\Cache;

use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Wraps a PSR-16 Simple Cache implementation and swallows all runtime exceptions (optionally logging them).
 */
class SafeSimpleCache implements CacheInterface, LoggerAwareInterface
{
    /** @var CacheInterface */
    protected $cache;
    /** @var bool */
    protected $debug;
    /** @var LoggerInterface */
    protected $logger;

    /**
     * Constructor.
     *
     * @param CacheInterface  $cache
     * @param bool            $debug
     * @param LoggerInterface $logger
     */
    public function __construct(CacheInterface $cache, $debug = false, LoggerInterface $logger = null)
    {
        $this->cache = $cache;
        $this->debug = $debug;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        if ($this->cache instanceof LoggerAwareInterface) {
            $this->cache->setLogger($logger);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        try {
            return $this->cache->get($key);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->logger->error('Failed to get the cache item.', array(
                'key'       => $key,
                'exception' => $e,
            ));

            return $default;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
    {
        try {
            return $this->cache->set($key, $value, $ttl);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->logger->error('Failed to set the cache item.', array(
                'key'       => $key,
                'exception' => $e,
            ));

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        try {
            return $this->cache->delete($key);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->logger->error('Failed to delete the cache item.', array(
                'key'       => $key,
                'exception' => $e,
            ));

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        try {
            return $this->cache->clear();
        } catch (Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->logger->error('Failed to clear the cache.', array(
                'exception' => $e,
            ));

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple($keys, $default = null)
    {
        try {
            return $this->cache->getMultiple($keys);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->logger->error('Failed to get the cache items.', array(
                'keys'      => $keys,
                'exception' => $e,
            ));

            return array_fill_keys($keys, $default);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        try {
            return $this->cache->setMultiple($values, $ttl);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->logger->error('Failed to set the cache items.', array(
                'exception' => $e,
            ));

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys)
    {
        try {
            return $this->cache->deleteMultiple($keys);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->logger->error('Failed to delete the cache items.', array(
                'keys'      => $keys,
                'exception' => $e,
            ));

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        try {
            return $this->cache->has($key);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->logger->error('Failed check if the cache has the item.', array(
                'key'       => $key,
                'exception' => $e,
            ));

            return false;
        }
    }
}

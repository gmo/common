<?php

namespace Gmo\Common\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * Wraps a PSR-6 Cache implementation and swallows all runtime exceptions (optionally logging them).
 */
class SafeCache implements CacheItemPoolInterface, LoggerAwareInterface, TagAwareAdapterInterface
{
    /** @var CacheItemPoolInterface */
    protected $cache;
    /** @var bool */
    protected $debug;
    /** @var LoggerInterface */
    protected $logger;
    /** @var CacheItemPoolInterface */
    protected $nullCache;

    /**
     * Constructor.
     *
     * @param CacheItemPoolInterface $cache
     * @param bool                   $debug
     * @param LoggerInterface        $logger
     */
    public function __construct(CacheItemPoolInterface $cache, $debug = false, LoggerInterface $logger = null)
    {
        $this->cache = $cache;
        $this->debug = $debug;
        $this->logger = $logger ?: new NullLogger();
        if (!$debug) {
            $this->nullCache = new NullAdapter();
        }
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
    public function getItem($key)
    {
        try {
            return $this->cache->getItem($key);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->logger->error('Failed to get the cache item.', array(
                'key'       => $key,
                'exception' => $e,
            ));
        }

        return $this->nullCache->getItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = array())
    {
        try {
            return $this->cache->getItems($keys);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->logger->error('Failed to get the cache items.', array(
                'keys'       => $keys,
                'exception' => $e,
            ));
        }

        return $this->nullCache->getItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        try {
            return $this->cache->hasItem($key);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->logger->error('Failed to check if cache has the item.', array(
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
        } catch (\Exception $e) {
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
    public function deleteItem($key)
    {
        try {
            return $this->cache->deleteItem($key);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
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
    public function deleteItems(array $keys)
    {
        try {
            return $this->cache->deleteItems($keys);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
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
    public function save(CacheItemInterface $item)
    {
        try {
            return $this->cache->save($item);
        } catch (\Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->logger->error('Failed to save the cache item.', array(
                'exception' => $e,
            ));

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        try {
            return $this->cache->saveDeferred($item);
        } catch (\Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->logger->error('Failed to defer saving of the cache item.', array(
                'exception' => $e,
            ));

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        try {
            return $this->cache->commit();
        } catch (\Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->logger->error('Failed to save deferred cache items.', array(
                'exception' => $e,
            ));

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateTags(array $tags)
    {
        if (!$this->cache instanceof TagAwareAdapterInterface) {
            throw new \LogicException('Given cache does not implement Symfony\Component\Cache\Adapter\TagAwareAdapterInterface');
        }

        try {
            return $this->cache->invalidateTags($tags);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            $this->logger->error('Failed to invalidate the tags.', array(
                'tags'      => $tags,
                'exception' => $e,
            ));

            return false;
        }
    }
}

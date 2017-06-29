<?php

namespace Gmo\Common\Cache;

use Carbon\Carbon;
use GMO\Common\Collections\ArrayCollection;
use Predis;
use Predis\Command\CommandInterface;
use Predis\NotSupportedException;

/**
 * An array implementation for Predis.
 *
 * Only a subset of the functionality is currently implemented.
 *
 * Most of Keys, Strings, Hashes, Lists are implemented,
 * with the exception of bit, blocking, and misc debugging methods.
 *
 * Basically anything we use in code is implemented.
 */
class ArrayPredis implements Predis\ClientInterface
{
    /** @var ArrayCollection */
    protected $data;
    /** @var ArrayCollection */
    protected $expiring;
    /** @var ArrayCollection */
    protected $pubSub;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->flushdb();
        $this->pubSub = new ArrayCollection();
    }

    public function flushdb()
    {
        $this->data = new ArrayCollection();
        $this->expiring = new ArrayCollection();
    }

    public function flushall()
    {
        $this->flushdb();
    }

    //region Keys

    protected function doExpire()
    {
        if ($this->expiring->isEmpty()) {
            return;
        }

        $now = $this->getTimestamp();
        foreach ($this->expiring as $key => $time) {
            if ($now >= $time) {
                unset($this->expiring[$key]);
                unset($this->data[$key]);
            }
        }
    }

    public function del($keys, $key2 = null, $key3 = null)
    {
        $this->doExpire();

        $keys = $this->normalizeArgs(func_get_args());
        $count = 0;
        foreach ($keys as $key) {
            if (isset($this->data[$key])) {
                $count++;
            }
            unset($this->data[$key]);
        }

        return $count;
    }

    public function dump($key)
    {
        throw new NotSupportedException();
    }

    public function exists($key)
    {
        return $this->doExists($key);
    }

    protected function doExists($key, $runExpiration = true)
    {
        if ($runExpiration) {
            $this->doExpire();
        }

        return isset($this->data[$key]);
    }

    public function expire($key, $seconds)
    {
        if (!$this->doExists($key)) {
            return 0;
        }

        $this->expiring[$key] = $this->getTimestamp() + $seconds;

        return 1;
    }

    public function expireat($key, $timestamp)
    {
        if (!$this->doExists($key)) {
            return 0;
        }

        $this->expiring[$key] = $timestamp;

        return 1;
    }

    public function pexpire($key, $milliseconds)
    {
        if (!$this->doExists($key)) {
            return 0;
        }

        $this->expiring[$key] = $this->getTimestamp() + ceil($milliseconds / 1000);

        return 1;
    }

    public function pexpireat($key, $timestamp)
    {
        if (!$this->doExists($key)) {
            return 0;
        }

        $this->expiring[$key] = ceil($timestamp / 1000);

        return 1;
    }

    public function keys($pattern)
    {
        $this->doExpire();

        return Redis\Glob::filter($pattern, $this->data->getKeys());
    }

    public function move($key, $db)
    {
        throw new NotSupportedException();
    }

    public function object($subcommand, $key)
    {
        throw new NotSupportedException();
    }

    public function persist($key)
    {
        if (!$this->doExists($key)) {
            return 0;
        }

        if (!$this->expiring->containsKey($key)) {
            return 0;
        }

        $this->expiring->remove($key);

        return 1;
    }

    public function ttl($key)
    {
        if (!$this->doExists($key)) {
            return -2;
        }
        if (!$this->expiring->containsKey($key)) {
            return -1;
        }

        return $this->expiring->get($key) - $this->getTimestamp();
    }

    public function pttl($key)
    {
        if (!$this->doExists($key)) {
            return -2;
        }
        if (!$this->expiring->containsKey($key)) {
            return -1;
        }

        return ($this->expiring->get($key) - $this->getTimestamp()) * 1000;
    }

    public function randomkey()
    {
        $this->doExpire();

        return array_rand($this->data->toArray());
    }

    public function rename($key, $target)
    {
        if ($key == $target) {
            throw new Predis\Response\ServerException('ERR source and destination objects are the same');
        }

        if (!$this->doExists($key)) {
            throw new Predis\Response\ServerException('ERR no such key');
        }

        $this->doRename($key, $target);

        return 'OK';
    }

    public function renamenx($key, $target)
    {
        if ($key == $target) {
            throw new Predis\Response\ServerException('ERR source and destination objects are the same');
        }

        if (!$this->doExists($key)) {
            throw new Predis\Response\ServerException('ERR no such key');
        }

        if ($this->doExists($target, false)) {
            return 0;
        }

        $this->doRename($key, $target);

        return 1;
    }

    protected function doRename($key, $target)
    {
        $item = $this->data->remove($key);
        $this->data->set($target, $item);

        if ($this->expiring->containsKey($key)) {
            $time = $this->expiring->remove($key);
            $this->expiring->set($target, $time);
        }
    }

    public function sort($key, array $options = null)
    {
        throw new NotSupportedException();
    }

    public function type($key)
    {
        throw new NotSupportedException();
    }

    public function scan($cursor, array $options = null)
    {
        throw new NotSupportedException();
    }

    //endregion

    //region Strings

    public function append($key, $value)
    {
        $this->doExpire();

        $this->data[$key] .= $value;

        return $this->strlen($key);
    }

    public function incr($key)
    {
        return $this->incrby($key, 1);
    }

    public function incrbyfloat($key, $increment)
    {
        return $this->incrby($key, $increment);
    }

    public function incrby($key, $value)
    {
        if (!$this->doExists($key)) {
            $this->data[$key] = 0;
        }

        return $this->data[$key] += $value;
    }

    public function decr($key)
    {
        return $this->decrby($key, 1);
    }

    public function decrby($key, $value)
    {
        if (!$this->doExists($key)) {
            $this->data[$key] = 0;
        }

        return $this->data[$key] -= $value;
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function get($key)
    {
        $this->doExpire();

        if (!isset($this->data[$key])) {
            return null;
        }

        return $this->data[$key];
    }

    public function getrange($key, $start, $end)
    {
        $value = $this->get($key);
        if ($end < 0) {
            $end += mb_strlen($value);
        }
        return mb_substr($this->get($key), $start, $end);
    }

    public function getset($key, $value)
    {
        $ret = $this->get($key);
        $this->set($key, $value);

        return $ret;
    }

    /**
     * SET key value [NX|XX] [EX|PX ttl]
     *
     * @param string     $key
     * @param string     $value
     * @param null $expireResolution
     * @param null $expireTTL
     * @param null $flag
     *
     * @return null|string
     */
    public function set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null)
    {
        $expireResolution = strtolower($expireResolution);
        $expireTTL = strtolower($expireTTL);

        $condition = null;
        $ttl = 0;
        if (in_array($expireResolution, array('nx', 'xx'))) {
            $condition = $expireResolution;
            if ($expireTTL === 'ex') {
                $ttl = $flag;
            } elseif ($expireTTL === 'px') {
                $ttl = ceil($flag / 1000);
            }
        } else {
            if ($expireResolution === 'ex') {
                $ttl = $expireTTL;
            } elseif ($expireResolution === 'px') {
                $ttl = ceil($expireTTL / 1000);
            }
        }

        if ($condition === 'nx' && $this->doExists($key)) {
            return null;
        }
        if ($condition === 'xx' && !$this->doExists($key)) {
            return null;
        }

        $this->data[$key] = $value;
        if ($ttl > 0) {
            $this->expiring[$key] = $this->getTimestamp() + $ttl;
        }

        return 'OK';
    }

    public function setex($key, $seconds, $value)
    {
        $this->data[$key] = $value;
        $this->expiring[$key] = $this->getTimestamp() + $seconds;

        return 'OK';
    }

    public function psetex($key, $milliseconds, $value)
    {
        $this->data[$key] = $value;
        $this->expiring[$key] = $this->getTimestamp() + ceil($milliseconds / 1000);

        return 'OK';
    }

    public function setnx($key, $value)
    {
        if (isset($this->data[$key])) {
            return 0;
        }

        $this->data[$key] = $value;

        return 1;
    }

    public function msetnx(array $dictionary)
    {
        foreach ($dictionary as $key => $value) {
            if ($this->doExists($key)) {
                return 0;
            }
        }

        $this->mset($dictionary);

        return 1;
    }

    public function setrange($key, $offset, $value)
    {
        $old = $this->get($key);
        $string = str_pad(mb_substr($old, 0, $offset), $offset, "\0") . $value;

        $this->data[$key] = $string;

        return mb_strlen($string);
    }

    public function mset(array $dictionary)
    {
        $this->data->replace($dictionary);

        return 'OK';
    }

    public function mget($keys)
    {
        $keys = $this->normalizeArgs(func_get_args());
        $this->doExpire();

        $values = array();

        foreach ($keys as $key) {
            $values[] = $this->get($key);
        }

        return $values;
    }

    public function strlen($key)
    {
        $this->doExpire();

        return mb_strlen($this->get($key));
    }

    public function bitcount($key, $start = null, $end = null)
    {
        throw new NotSupportedException();
    }

    public function bitop($operation, $destkey, $key)
    {
        throw new NotSupportedException();
    }

    public function getbit($key, $offset)
    {
        throw new NotSupportedException();
    }

    public function setbit($key, $offset, $value)
    {
        throw new NotSupportedException();
    }

    //endregion

    //region Hashes

    public function hset($key, $field, $value)
    {
        if (!$this->isTraversable($key)) {
            $this->data[$key] = new ArrayCollection();
        }
        $isNew = $this->data[$key]->containsKey($field);
        $this->data[$key][$field] = $value;

        return !$isNew;
    }

    public function hsetnx($key, $field, $value)
    {
        if ($this->hexists($key, $field)) {
            return 0;
        }

        $this->hset($key, $field, $value);

        return 1;
    }

    public function hget($key, $field)
    {
        $this->doExpire();

        if (isset($this->data[$key][$field])) {
            return $this->data[$key][$field];
        }

        return null;
    }

    public function hlen($key)
    {
        $this->doExpire();

        if (!$this->isTraversable($key)) {
            return 0;
        }

        return count($this->data[$key]);
    }

    public function hdel($key, $fields)
    {
        if (!is_array($fields)) {
            $fields = func_get_args();
            array_shift($fields);
        }

        $this->doExpire();

        if (!$this->isTraversable($key)) {
            return 0;
        }

        $count = 0;
        foreach ($fields as $field) {
            if (isset($this->data[$key][$field])) {
                $count++;
                unset($this->data[$key][$field]);
            }
        }

        return $count;
    }

    public function hkeys($key)
    {
        $this->doExpire();

        if (!$this->isTraversable($key)) {
            return array();
        }

        return $this->data[$key]->getKeys()->toArray();
    }

    public function hvals($key)
    {
        $this->doExpire();

        if (!$this->isTraversable($key)) {
            return array();
        }

        return $this->data[$key]->getValues()->toArray();
    }

    public function hgetall($key)
    {
        if (!$this->isTraversable($key)) {
            return array();
        }

        return $this->data->get($key)->toArray();
    }

    public function hexists($key, $field)
    {
        $this->doExpire();

        return isset($this->data[$key][$field]);
    }

    public function hincrby($key, $field, $increment)
    {
        if (!$this->isTraversable($key)) {
            $this->data[$key] = new ArrayCollection();
        }
        if (!isset($this->data[$key][$field])) {
            $this->data[$key][$field] = 0;
        }

        return $this->data[$key][$field] += $increment;
    }

    public function hincrbyfloat($key, $field, $increment)
    {
        return $this->hincrby($key, $field, $increment);
    }

    public function hmset($key, array $dictionary)
    {
        if (!$this->isTraversable($key)) {
            $this->data[$key] = new ArrayCollection();
        }
        foreach ($dictionary as $hashKey => $value) {
            $this->data[$key][$hashKey] = $value;
        }

        return 'OK';
    }

    public function hmget($key, array $fields)
    {
        $this->doExpire();

        if (!$this->isTraversable($key)) {
            return array_pad(array(), count($fields), null);
        }

        $values = array();
        foreach ($fields as $field) {
            $values[] = $this->hget($key, $field);
        }

        return $values;
    }

    public function hscan($key, $cursor, array $options = null)
    {
        throw new NotSupportedException();
    }

    //endregion

    //region Lists

    public function lpush($key, $values)
    {
        if (!is_array($values)) {
            $values = func_get_args();
            array_shift($values);
        }

        $this->ensureSubCollection($key);

        foreach ($values as $value) {
            $this->data[$key]->prepend($value);
        }

        return $this->data[$key]->count();
    }

    public function lpushx($key, $value)
    {
        if (!$this->isTraversable($key) || $this->data[$key]->isEmpty()) {
            return 0;
        }

        $this->data[$key]->prepend($value);

        return $this->data[$key]->count();
    }

    public function rpush($key, $values)
    {
        if (!is_array($values)) {
            $values = func_get_args();
            array_shift($values);
        }

        $this->ensureSubCollection($key);

        foreach ($values as $value) {
            $this->data[$key]->add($value);
        }

        return $this->data[$key]->count();
    }

    public function rpushx($key, $value)
    {
        if (!$this->isTraversable($key) || $this->data[$key]->isEmpty()) {
            return 0;
        }

        $this->data[$key]->add($value);

        return $this->data[$key]->count();
    }

    public function lpop($key)
    {
        if (!$this->isTraversable($key)) {
            return null;
        }

        $value = $this->data[$key]->removeFirst();

        $this->resetListIndex($key);

        return $value;
    }

    public function rpop($key)
    {
        if (!$this->isTraversable($key)) {
            return null;
        }

        $value = $this->data[$key]->removeLast();

        $this->resetListIndex($key);

        return $value;
    }

    public function blpop(array $keys, $timeout)
    {
        throw new NotSupportedException();
    }

    public function brpop(array $keys, $timeout)
    {
        throw new NotSupportedException();
    }

    public function llen($key)
    {
        if (!$this->isTraversable($key)) {
            return 0;
        }

        return $this->data[$key]->count();
    }

    public function lindex($key, $index)
    {
        if (!$this->isTraversable($key)) {
            return null;
        }
        $sub = $this->data[$key];

        if ($index < 0) {
            $index += $sub->count();
        }

        return $sub->get($index);
    }

    public function lset($key, $index, $value)
    {
        if (!$this->isTraversable($key)) {
            throw new Predis\Response\ServerException('ERR no such key');
        }

        $sub = $this->data[$key];

        if ($index < 0) {
            $index += $sub->count();
        }

        if (!$sub->containsKey($index)) {
            throw new Predis\Response\ServerException('ERR index out of range');
        }

        $sub[$index] = $value;

        $this->resetListIndex($key);

        return 'OK';
    }

    public function lrange($key, $start, $stop)
    {
        if (!$this->isTraversable($key)) {
            return array();
        }

        $sub = $this->data[$key];

        if ($start < 0) {
            $start += $sub->count();
        }
        if ($stop < 0) {
            $stop += $sub->count();
        }

        return $sub->slice($start, $stop + 1)->getValues()->toArray();
    }

    public function ltrim($key, $start, $stop)
    {
        if (!$this->isTraversable($key)) {
            return 'OK';
        }

        $this->data[$key] = new ArrayCollection($this->lrange($key, $start, $stop));

        return 'OK';
    }

    public function lrem($key, $count, $value)
    {
        if (!$this->isTraversable($key)) {
            return 0;
        }

        $sub = $this->data[$key];

        $numToRemove = abs($count);
        $removed = 0;
        if ($count < 0) {
            for ($i = $sub->count() - 1; $i >= 0; $i--) {
                if ($numToRemove === 0) {
                    break;
                }
                if ($sub->get($i) === $value) {
                    $sub->remove($i);
                    $removed++;
                    $numToRemove--;
                }
            }
        } elseif ($count > 0) {
            for ($i = 0; $i >= 0; $i++) {
                if ($numToRemove === 0) {
                    break;
                }
                if ($sub->get($i) === $value) {
                    $sub->remove($i);
                    $removed++;
                    $numToRemove--;
                }
            }
        } else {
            foreach ($sub as $index => $val) {
                if ($value === $val) {
                    $sub->remove($index);
                    $removed++;
                }
            }
        }

        if ($removed > 0) {
            $this->resetListIndex($key);
        }

        return $removed;
    }

    public function linsert($key, $whence, $pivot, $value)
    {
        if (!$this->isTraversable($key)) {
            return 0;
        }

        $whence = strtolower($whence);

        $sub = $this->data[$key];

        $index = $sub->indexOf($pivot);
        if ($index === false) {
            return -1;
        }

        if ($whence === 'before') {
            $firstPart = $sub->slice(0, $index);
            $secondPart = $sub->slice($index);
        } else {
            $firstPart = $sub->slice(0, $index + 1);
            $secondPart = $sub->slice($index + 1);
        }

        $this->data[$key] = ArrayCollection::create($firstPart)->add($value)->merge($secondPart);

        return $this->data[$key]->count();
    }

    public function rpoplpush($source, $destination)
    {
        $value = $this->rpop($source);
        if ($value !== null) {
            $this->lpush($destination, $value);
        }

        return $value;
    }

    public function brpoplpush($source, $destination, $timeout)
    {
        throw new NotSupportedException();
    }

    //endregion

    public function multi()
    {
        throw new NotSupportedException();
    }

    public function exec()
    {
        throw new NotSupportedException();
    }

    public function discard()
    {
        throw new NotSupportedException();
    }

    public function watch($key)
    {
        throw new NotSupportedException();
    }

    public function unwatch()
    {
        throw new NotSupportedException();
    }

    //region Pub/Sub

    public function subscribe($channels, $callback)
    {
        throw new NotSupportedException();
    }

    public function psubscribe($patterns, $callback)
    {
        throw new NotSupportedException();
    }

    public function publish($channel, $message)
    {
        if (!$this->pubSub->containsKey($channel)) {
            $this->pubSub[$channel] = new ArrayCollection();
        }
        $this->pubSub[$channel][] = $message;
    }

    public function getMessages($channel)
    {
        if (!$this->pubSub->containsKey($channel)) {
            return new ArrayCollection();
        }

        return $this->pubSub[$channel];
    }

    //endregion


    public function executeCommand(CommandInterface $command)
    {
        throw new NotSupportedException();
    }

    public function getProfile()
    {
        throw new NotSupportedException();
    }

    public function getOptions()
    {
        throw new NotSupportedException();

    }

    public function connect()
    {
        throw new NotSupportedException();
    }

    public function disconnect()
    {
        throw new NotSupportedException();

    }

    public function getConnection()
    {
        throw new NotSupportedException();

    }

    public function createCommand($method, $arguments = array())
    {
        throw new NotSupportedException();
    }

    public function __call($method, $arguments)
    {
        throw new NotSupportedException();
    }

    protected function ensureSubCollection($key)
    {
        if (!$this->isTraversable($key)) {
            $this->data[$key] = new ArrayCollection();
        }
    }

    protected function isTraversable($key)
    {
        return $this->doExists($key) && is_iterable($this->data[$key]);
    }

    protected function normalizeArgs(array $args)
    {
        if (count($args) === 1 && is_array($args[0])) {
            return $args[0];
        }

        return $args;
    }

    protected function resetListIndex($key)
    {
        $this->data[$key] = $this->data[$key]->getValues();
    }

    protected function getTimestamp()
    {
        return Carbon::now()->timestamp;
    }
}

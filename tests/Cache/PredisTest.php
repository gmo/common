<?php

namespace Gmo\Common\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Predis;

class PredisTest extends TestCase
{
    /** @var Predis\ClientInterface */
    protected $client;

    protected function setUp()
    {
        parent::setUp();
        $this->client = $this->createClient();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->client->flushdb();
    }

    public function createClient()
    {
        $client = new Predis\Client();
        $client->select(10);
        return $client;
    }

    //region Keys

    /**
     * @group redis-keys
     */
    public function testDelete()
    {
        $this->client->set('foo', 'bar');
        $this->client->set('hello', 'world');

        $this->client->del('foo', 'hello');
        $this->assertEquals(0, $this->client->exists('foo'));
        $this->assertEquals(0, $this->client->exists('hello'));

        $this->client->set('foo', 'bar');
        $this->client->set('hello', 'world');

        $this->client->del(array('foo', 'hello'));
        $this->assertEquals(0, $this->client->exists('foo'));
        $this->assertEquals(0, $this->client->exists('hello'));
    }

    ///**
    // * @group redis-keys
    // */
    //public function testDump()
    //{
    //    $this->markTestSkipped();
    //}

    /**
     * @group redis-keys
     */
    public function testExists()
    {
        $this->assertEquals(0, $this->client->exists('foo'));
        $this->client->set('foo', 'bar');
        $this->assertEquals(1, $this->client->exists('foo'));
    }

    /**
     * @group redis-keys
     */
    public function testExpire()
    {
        $this->assertEquals(0, $this->client->expire('foo', 1));

        $this->client->set('foo', 'bar');
        $this->assertEquals(1, $this->client->expire('foo', 1));
        usleep(1.5e+6);
        $this->assertEquals(0, $this->client->exists('foo'));
    }

    /**
     * @group redis-keys
     */
    public function testPreciseExpire()
    {
        $this->assertEquals(0, $this->client->expire('foo', 1));

        $this->client->set('foo', 'bar');
        $this->assertEquals(1, $this->client->pexpire('foo', 500));
        usleep(1.5e+6);
        $this->assertEquals(0, $this->client->exists('foo'));
    }

    /**
     * @group redis-keys
     */
    public function testExpireAt()
    {
        $this->assertEquals(0, $this->client->expire('foo', 1));

        $this->client->set('foo', 'bar');
        $this->assertEquals(1, $this->client->expireat('foo', time() + 1));
        usleep(1.5e+6);
        $this->assertEquals(0, $this->client->exists('foo'));
    }

    /**
     * @group redis-keys
     */
    public function testPreciseExpireAt()
    {
        $this->assertEquals(0, $this->client->expire('foo', 1));

        $this->client->set('foo', 'bar');
        $this->client->pexpireat('foo', (time() + 1) * 1000);
        usleep(1.5e+6);
        $this->assertEquals(0, $this->client->exists('foo'));
    }

    /**
     * @group redis-keys
     */
    public function testKeys()
    {
        //$this->assertEquals(array(), $this->client->keys('derp'));

        $items = array(
            'hello'       => 1,
            'hallo'       => 1,
            'hxllo'       => 1,
            'hllo'        => 1,
            'heeeello'    => 1,
            'hillo'       => 1,
            'hbllo'       => 1,
            'color:red'   => 'red',
            'color:blue'  => 'blue',
            'color:green' => 'green',
            '[ns]foo'     => 'bar',
        );
        $this->client->mset($items);

        $this->assertArraySimilar(array('hello', 'hallo', 'hxllo', 'hbllo', 'hillo'), $this->client->keys('h?llo'));
        $this->assertArraySimilar(array('hllo', 'hello', 'hallo', 'hxllo', 'hillo', 'hbllo', 'heeeello'), $this->client->keys('h*llo'));
        $this->assertArraySimilar(array('hello', 'hallo'), $this->client->keys('h[ae]llo'));
        $this->assertArraySimilar(array('hallo', 'hillo', 'hbllo', 'hxllo'), $this->client->keys('h[^e]llo'));
        $this->assertArraySimilar(array('hallo', 'hbllo'), $this->client->keys('h[a-b]llo'));
        $this->assertArraySimilar(array('color:green'), $this->client->keys('*en'));
        $this->assertArraySimilar(array('[ns]foo'), $this->client->keys('\[ns\]*'));
        $this->assertArraySimilar(array('color:red'), $this->client->keys('color:red'));

        $this->assertArraySimilar(array_keys($items), $this->client->keys('*'));
    }

    protected function assertArraySimilar(array $expected, array $actual)
    {
        $this->assertSame(array_diff($expected, $actual), array_diff($actual, $expected));
    }

    ///**
    // * @group redis-keys
    // */
    //public function testMove()
    //{
    //    $this->markTestSkipped();
    //}
    //
    ///**
    // * @group redis-keys
    // */
    //public function testObject()
    //{
    //    $this->markTestSkipped();
    //}

    /**
     * @group redis-keys
     */
    public function testPersist()
    {
        $this->assertEquals(0, $this->client->persist('foo'));

        $this->client->set('foo', 'bar');
        $this->assertEquals(0, $this->client->persist('foo'));

        $this->client->expire('foo', 10);
        $this->assertEquals(1, $this->client->persist('foo'));
        $this->assertSame(-1, $this->client->ttl('foo'));
    }

    /**
     * @group redis-keys
     */
    public function testTtl()
    {
        $this->assertSame(-2, $this->client->ttl('foo'));

        $this->client->set('foo', 'bar');
        $this->assertSame(-1, $this->client->ttl('foo'));

        $this->client->expire('foo', 20);
        $this->assertThat(
            $this->client->ttl('foo'),
            $this->logicalAnd($this->greaterThan(0), $this->lessThanOrEqual(20))
        );
    }

    /**
     * @group redis-keys
     */
    public function testPreciseTtl()
    {
        $this->assertSame(-2, $this->client->ttl('foo'));

        $this->client->set('foo', 'bar');
        $this->assertSame(-1, $this->client->ttl('foo'));

        $this->client->expire('foo', 20);
        $this->assertThat(
            $this->client->pttl('foo'),
            $this->logicalAnd($this->greaterThan(0), $this->lessThanOrEqual(20000))
        );
    }

    /**
     * @group redis-keys
     */
    public function testRandomKey()
    {
        $items = array(
            'hello' => 'world',
            'foo'   => 'bar',
        );
        $this->client->mset($items);

        $this->assertContains($this->client->randomkey(), array_keys($items));
    }

    /**
     * @group redis-keys
     */
    public function testRename()
    {
        try {
            $this->client->rename('foo', 'bar');
            $this->fail('rename should throw exception when source does not exist');
        } catch (Predis\Response\ServerException $e) {
            if ($e->getMessage() !== 'ERR no such key') {
                throw $e;
            }
        }

        $this->client->set('foo', 'bar');

        $this->assertEquals('OK', $this->client->rename('foo', 'foo'));

        $this->assertEquals('OK', $this->client->rename('foo', 'baz'));
        $this->assertEquals(1, $this->client->exists('baz'));
        $this->assertEquals(0, $this->client->exists('foo'));
    }

    /**
     * @group redis-keys
     */
    public function testRenameNx()
    {
        try {
            $this->client->renamenx('foo', 'bar');
            $this->fail('rename should throw exception when source does not exist');
        } catch (Predis\Response\ServerException $e) {
            if ($e->getMessage() !== 'ERR no such key') {
                throw $e;
            }
        }

        $this->client->set('hello', 'world');
        $this->client->set('foo', 'bar');

        $this->assertEquals(0, $this->client->renamenx('foo', 'foo'));

        $this->assertEquals(0, $this->client->renamenx('foo', 'hello'));

        $this->assertEquals(1, $this->client->renamenx('foo', 'baz'));
        $this->assertEquals(1, $this->client->exists('baz'));
        $this->assertEquals(0, $this->client->exists('foo'));
    }

    ///**
    // * @group redis-keys
    // */
    //public function testRestore()
    //{
    //    $this->markTestSkipped();
    //}
    //
    ///**
    // * @group redis-keys
    // */
    //public function testScan()
    //{
    //    $this->markTestSkipped();
    //}
    //
    ///**
    // * @group redis-keys
    // */
    //public function testSort()
    //{
    //    $this->markTestSkipped();
    //}
    //
    ///**
    // * @group redis-keys
    // */
    //public function testType()
    //{
    //    $this->markTestSkipped();
    //}

    //endregion

    //region Strings

    /**
     * @group redis-strings
     */
    public function testAppend()
    {
        $this->assertSame(5, $this->client->append('foo', 'Hello'));
        $this->assertSame(11, $this->client->append('foo', ' World'));
        $this->assertSame('Hello World', $this->client->get('foo'));
    }

    ///**
    // * @group redis-strings
    // */
    //public function testBitCount()
    //{
    //    $this->markTestSkipped();
    //}
    //
    ///**
    // * @group redis-strings
    // */
    //public function testBitOp()
    //{
    //    $this->markTestSkipped();
    //}

    /**
     * @group redis-strings
     */
    public function testDecrement()
    {
        $this->assertEquals(-1, $this->client->decr('foo'));
        $this->assertEquals(-2, $this->client->decr('foo'));
    }

    /**
     * @group redis-strings
     */
    public function testDecrementBy()
    {
        $this->assertEquals(-2, $this->client->decrby('foo', 2));
        $this->assertEquals(-4, $this->client->decrby('foo', 2));
    }

    ///**
    // * @group redis-strings
    // */
    //public function testGetBit()
    //{
    //    $this->markTestSkipped();
    //}

    /**
     * @group redis-strings
     */
    public function testGetRange()
    {
        $this->assertSame('', $this->client->getrange('foo', 0, -1));
        $this->client->set('foo', 'Hello World');
        $this->assertSame('ello Worl', $this->client->getrange('foo', 1, -2));
        $this->assertSame('World', $this->client->getrange('foo', -5, -1));
    }

    /**
     * @group redis-strings
     */
    public function testGetSet()
    {
        $this->assertNull($this->client->getset('foo', 'bar'));
        $this->assertSame('bar', $this->client->getset('foo', 'derp'));
        $this->assertSame('derp', $this->client->get('foo'));
    }

    /**
     * @group redis-strings
     */
    public function testIncrement()
    {
        $this->assertEquals(1, $this->client->incr('foo'));
        $this->assertEquals(2, $this->client->incr('foo'));
    }

    /**
     * @group redis-strings
     */
    public function testIncrementByFloat()
    {
        $this->assertEquals(1.5, $this->client->incrbyfloat('foo', 1.5));
        $this->assertEquals(3.0, $this->client->incrbyfloat('foo', 1.5));
    }

    /**
     * @group redis-strings
     */
    public function testIncrementBy()
    {
        $this->assertEquals(2, $this->client->incrby('foo', 2));
        $this->assertEquals(4, $this->client->incrby('foo', 2));
    }

    /**
     * @group redis-strings
     */
    public function testMultipleSet()
    {
        $this->assertEquals('OK', $this->client->mset(array(
            'foo'   => 'bar',
            'hello' => 'world',
        )));
        $this->assertSame('bar', $this->client->get('foo'));
        $this->assertSame('world', $this->client->get('hello'));
    }

    /**
     * @group redis-strings
     */
    public function testMultipleGet()
    {
        $this->client->mset(array(
            'foo'   => 'bar',
            'hello' => 'world',
        ));
        $this->assertEquals(array('bar', 'world'), $this->client->mget('foo', 'hello'));
    }

    /**
     * @group redis-strings
     */
    public function testMultipleSetNx()
    {
        $this->assertEquals(1, $this->client->msetnx(array(
            'foo'   => 'bar',
            'hello' => 'world',
        )));
        $this->assertEquals(0, $this->client->msetnx(array(
            'foo' => 'baz',
            'red' => 'blue',
        )));
        $this->assertSame('bar', $this->client->get('foo'));
        $this->assertSame('world', $this->client->get('hello'));
        $this->assertEquals(0, $this->client->exists('red'));
    }

    /**
     * @group redis-strings
     */
    public function testGet()
    {
        $this->assertNull($this->client->get('foo'));
        $this->client->set('foo', 'bar');
        $this->assertSame('bar', $this->client->get('foo'));
    }

    /**
     * @group redis-strings
     */
    public function testSet()
    {
        $this->assertEquals('OK', $this->client->set('foo', 'bar'));
        $this->assertEquals('OK', $this->client->set('foo', 'baz'));
        $this->assertSame('baz', $this->client->get('foo'));

        // EX expire time
        $this->assertEquals('OK', $this->client->set('foo', 'bar', 'EX', 20));
        $this->assertThat(
            $this->client->ttl('foo'),
            $this->logicalAnd($this->greaterThan(0), $this->lessThanOrEqual(20))
        );

        // PX expire time
        $this->client->del('foo');
        $this->assertEquals('OK', $this->client->set('foo', 'bar', 'PX', 20000));
        $this->assertThat(
            $this->client->ttl('foo'),
            $this->logicalAnd($this->greaterThan(0), $this->lessThanOrEqual(20))
        );

        $this->client->del('foo');
        $this->assertNull($this->client->set('foo', 'bar', 'XX'));
        $this->assertEquals('OK', $this->client->set('foo', 'baz', 'NX'));
        $this->assertNull($this->client->set('foo', 'blue', 'NX'));
        $this->assertEquals('OK', $this->client->set('foo', 'red', 'XX'));
    }

    ///**
    // * @group redis-strings
    // */
    //public function testSetBit()
    //{
    //    $this->markTestSkipped();
    //}

    /**
     * @group redis-strings
     */
    public function testSetEx()
    {
        $this->assertEquals('OK', $this->client->setex('foo', 20, 'bar'));
        $this->assertEquals('OK', $this->client->setex('foo', 20, 'baz'));
        $this->assertSame('baz', $this->client->get('foo'));
        $this->assertThat(
            $this->client->ttl('foo'),
            $this->logicalAnd($this->greaterThan(0), $this->lessThanOrEqual(20))
        );
    }

    /**
     * @group redis-strings
     */
    public function testPreciseSetEx()
    {
        $this->assertEquals('OK', $this->client->psetex('foo', 20000, 'bar'));
        $this->assertEquals('OK', $this->client->psetex('foo', 20000, 'baz'));
        $this->assertSame('baz', $this->client->get('foo'));
        $this->assertThat(
            $this->client->ttl('foo'),
            $this->logicalAnd($this->greaterThan(0), $this->lessThanOrEqual(20))
        );
    }

    /**
     * @group redis-strings
     */
    public function testSetNx()
    {
        $this->assertEquals(1, $this->client->setnx('foo', 'bar'));
        $this->assertEquals(0, $this->client->setnx('foo', 'bar'));
    }

    /**
     * @group redis-strings
     */
    public function testSetRange()
    {
        $this->assertSame(9, $this->client->setrange('foo', 4, 'World'));
        $this->assertSame("\0\0\0\0World", $this->client->get('foo'));

        $this->client->set('foo', 'Hello World');
        $this->assertSame(14, $this->client->setrange('foo', 6, 'Universe'));
        $this->assertSame('Hello Universe', $this->client->get('foo'));

        $this->client->set('foo', 'bar');
        $this->assertSame(9, $this->client->setrange('foo', 6, 'baz'));
        $this->assertSame("bar\0\0\0baz", $this->client->get('foo'));
    }

    /**
     * @group redis-strings
     */
    public function testStringLength()
    {
        $this->assertSame(0, $this->client->strlen('foo'));
        $this->client->set('foo', 'bar');
        $this->assertSame(3, $this->client->strlen('foo'));
    }

    //endregion

    //region Hashes

    /**
     * @group redis-hashes
     */
    public function testHashSet()
    {
        $this->assertEquals(1, $this->client->hset('foo', 'bar', 'hello'));
        $this->assertEquals('hello', $this->client->hget('foo', 'bar'));

        $this->assertEquals(0, $this->client->hset('foo', 'bar', 'world'));
        $this->assertEquals('world', $this->client->hget('foo', 'bar'));
    }

    /**
     * @group redis-hashes
     */
    public function testHashSetNx()
    {
        $this->assertEquals(1, $this->client->hsetnx('foo', 'bar', 'hello'));
        $this->assertEquals('hello', $this->client->hget('foo', 'bar'));

        $this->assertEquals(0, $this->client->hsetnx('foo', 'bar', 'world'));
        $this->assertEquals('hello', $this->client->hget('foo', 'bar'));
    }

    /**
     * @group redis-hashes
     */
    public function testHashGet()
    {
        $this->assertNull($this->client->hget('foo', 'bar'));

        $this->client->hset('foo', 'bar', 'hello');
        $this->assertEquals('hello', $this->client->hget('foo', 'bar'));
    }

    /**
     * @group redis-hashes
     */
    public function testHashLength()
    {
        $this->assertSame(0, $this->client->hlen('foo'));

        $this->client->hset('foo', 'hello', 'world');
        $this->client->hset('foo', 'bar', 'baz');
        $this->assertSame(2, $this->client->hlen('foo'));
    }

    /**
     * @group redis-hashes
     */
    public function testHashDelete()
    {
        $this->assertSame(0, $this->client->hdel('foo', 'bar'));

        $this->client->hset('foo', 'hello', 'world');
        $this->client->hset('foo', 'bar', 'baz');
        $this->client->hset('foo', 'red', 'blue');

        $this->assertSame(2, $this->client->hdel('foo', 'hello', 'bar', 'derp'));
        $this->assertEquals(array('red' => 'blue'), $this->client->hgetall('foo'));
    }

    /**
     * @group redis-hashes
     */
    public function testHashKeys()
    {
        $this->assertSame(array(), $this->client->hkeys('foo'));

        $this->client->hset('foo', 'hello', 'world');
        $this->client->hset('foo', 'bar', 'baz');
        $this->client->hset('foo', 'red', 'blue');

        $this->assertEquals(array('hello', 'bar', 'red'), $this->client->hkeys('foo'));
    }

    /**
     * @group redis-hashes
     */
    public function testHashValues()
    {
        $this->assertSame(array(), $this->client->hkeys('foo'));

        $this->client->hset('foo', 'hello', 'world');
        $this->client->hset('foo', 'bar', 'baz');
        $this->client->hset('foo', 'red', 'blue');

        $this->assertEquals(array('world', 'baz', 'blue'), $this->client->hvals('foo'));
    }

    /**
     * @group redis-hashes
     */
    public function testHashGetAll()
    {
        $this->assertSame(array(), $this->client->hgetall('foo'));

        $this->client->hset('foo', 'hello', 'world');
        $this->client->hset('foo', 'bar', 'baz');
        $this->client->hset('foo', 'red', 'blue');

        $expected = array(
            'hello' => 'world',
            'bar'   => 'baz',
            'red'   => 'blue',
        );
        $this->assertEquals($expected, $this->client->hgetall('foo'));
    }

    /**
     * @group redis-hashes
     */
    public function testHashExists()
    {
        $this->assertEquals(0, $this->client->hexists('foo', 'hello'));
        $this->client->hset('foo', 'hello', 'world');
        $this->assertEquals(1, $this->client->hexists('foo', 'hello'));
    }

    /**
     * @group redis-hashes
     */
    public function testHashIncrementBy()
    {
        $this->assertSame(2, $this->client->hincrby('foo', 'bar', 2));
        $this->assertSame(4, $this->client->hincrby('foo', 'bar', 2));
    }

    /**
     * @group redis-hashes
     */
    public function testHashIncrementByFloat()
    {
        $this->assertEquals(1.5, $this->client->hincrbyfloat('foo', 'bar', 1.5));
        $this->assertEquals(3.0, $this->client->hincrbyfloat('foo', 'bar', 1.5));
    }

    /**
     * @group redis-hashes
     */
    public function testHashMultipleSet()
    {
        $expected = array(
            'hello' => 'world',
            'red'   => 'blue',
        );
        $this->assertEquals('OK', $this->client->hmset('foo', $expected));
        $this->assertEquals($expected, $this->client->hgetall('foo'));
    }

    /**
     * @group redis-hashes
     */
    public function testHashMultipleGet()
    {
        $expected = array(
            'hello' => 'world',
            'red'   => 'blue',
            'herp'  => 'derp',
        );
        $this->client->hmset('foo', $expected);

        $this->assertEquals(array('world', 'blue'), $this->client->hmget('foo', array('hello', 'red')));
    }

    ///**
    // * @group redis-hashes
    // */
    //public function testHashScan()
    //{
    //    $this->markTestSkipped();
    //}

    //endregion

    //region Lists

    /**
     * @group redis-lists
     */
    public function testListLeftPush()
    {
        $this->assertEquals(2, $this->client->lpush('foo', 'world', 'hello'));
        $this->assertEquals(array('hello', 'world'), $this->client->lrange('foo', 0, -1));
        $this->assertEquals(3, $this->client->lpush('foo', 'derp'));
        $this->assertEquals(array('derp', 'hello', 'world'), $this->client->lrange('foo', 0, -1));
    }

    /**
     * @group redis-lists
     */
    public function testListRightPush()
    {
        $this->assertEquals(2, $this->client->rpush('foo', 'hello', 'world'));
        $this->assertEquals(array('hello', 'world'), $this->client->lrange('foo', 0, -1));
        $this->assertEquals(3, $this->client->rpush('foo', 'derp'));
        $this->assertEquals(array('hello', 'world', 'derp'), $this->client->lrange('foo', 0, -1));
    }

    /**
     * @group redis-lists
     */
    public function testListLeftPushExists()
    {
        $this->assertEquals(0, $this->client->lpushx('foo', 'bar'));
        $this->assertEquals(0, $this->client->exists('foo'));
        $this->client->lpush('foo', 'world');
        $this->assertEquals(2, $this->client->lpushx('foo', 'hello'));
        $this->assertEquals(array('hello', 'world'), $this->client->lrange('foo', 0, -1));
    }

    /**
     * @group redis-lists
     */
    public function testListRightPushExists()
    {
        $this->assertEquals(0, $this->client->rpushx('foo', 'bar'));
        $this->assertEquals(0, $this->client->exists('foo'));
        $this->client->rpush('foo', 'hello');
        $this->assertEquals(2, $this->client->rpushx('foo', 'world'));
        $this->assertEquals(array('hello', 'world'), $this->client->lrange('foo', 0, -1));
    }

    /**
     * @group redis-lists
     */
    public function testListLeftPop()
    {
        $this->assertNull($this->client->lpop('foo'));
        $this->client->rpush('foo', 'A', 'B', 'C');
        $this->assertSame('A', $this->client->lpop('foo'));
        $this->assertEquals(array('B', 'C'), $this->client->lrange('foo', 0, -1));
    }

    /**
     * @group redis-lists
     */
    public function testListRightPop()
    {
        $this->assertNull($this->client->rpop('foo'));
        $this->client->rpush('foo', 'A', 'B', 'C');
        $this->assertSame('C', $this->client->rpop('foo'));
        $this->assertEquals(array('A', 'B'), $this->client->lrange('foo', 0, -1));
    }

    ///**
    // * @group redis-lists
    // */
    //public function testListBlockLeftPop()
    //{
    //    $this->markTestSkipped();
    //}
    //
    ///**
    // * @group redis-lists
    // */
    //public function testListBlockRightPop()
    //{
    //    $this->markTestSkipped();
    //}

    /**
     * @group redis-lists
     */
    public function testListLength()
    {
        $this->assertSame(0, $this->client->llen('foo'));
        $this->client->rpush('foo', 'A', 'B', 'C');
        $this->assertSame(3, $this->client->llen('foo'));
    }

    /**
     * @group redis-lists
     */
    public function testListIndex()
    {
        $this->assertNull($this->client->lindex('foo', 0));
        $this->client->rpush('foo', 'A', 'B', 'C');

        $this->assertSame('A', $this->client->lindex('foo', 0));
        $this->assertSame('B', $this->client->lindex('foo', 1));
        $this->assertSame('C', $this->client->lindex('foo', 2));
        $this->assertSame('C', $this->client->lindex('foo', -1));
        $this->assertSame('B', $this->client->lindex('foo', -2));
    }

    /**
     * @group redis-lists
     */
    public function testListSet()
    {
        try {
            $this->client->lset('foo', 0, 'bar');
            $this->fail('lset on non-existent list should throw exception');
        } catch (Predis\Response\ServerException $e) {
            if ($e->getMessage() !== 'ERR no such key') {
                $this->fail('Wrong exception was thrown');
            }
        }

        $this->client->rpush('foo', 'A', 'B', 'C');
        $this->assertEquals('OK', $this->client->lset('foo', 0, 'bar'));
        $this->assertSame('bar', $this->client->lindex('foo', 0));

        try {
            $this->assertEquals('OK', $this->client->lset('foo', 100, 'bar'));
            $this->fail('lset with index out of range should throw exception');
        } catch (Predis\Response\ServerException $e) {
            if ($e->getMessage() !== 'ERR index out of range') {
                $this->fail('Wrong exception was thrown');
            }
        }
    }

    /**
     * @group redis-lists
     */
    public function testListRange()
    {
        $this->assertEquals(array(), $this->client->lrange('foo', 0, -1));
        $this->client->rpush('foo', 'A', 'B', 'C');

        $this->assertEquals(array('A'), $this->client->lrange('foo', 0, 0));
        $this->assertEquals(array('A', 'B'), $this->client->lrange('foo', 0, 1));
        $this->assertEquals(array('A', 'B', 'C'), $this->client->lrange('foo', 0, 2));
        $this->assertEquals(array('A', 'B', 'C'), $this->client->lrange('foo', 0, -1));
        $this->assertEquals(array('A', 'B'), $this->client->lrange('foo', 0, -2));
        $this->assertEquals(array('B', 'C'), $this->client->lrange('foo', 1, -1));
        $this->assertEquals(array('B', 'C'), $this->client->lrange('foo', -2, 2));
        $this->assertEquals(array('B', 'C'), $this->client->lrange('foo', -2, -1));
    }

    /**
     * @group redis-lists
     */
    public function testListTrim()
    {
        $this->assertEquals('OK', $this->client->ltrim('foo', 0, -1));
        $this->client->rpush('foo', 'A', 'B', 'C');

        $this->assertEquals('OK', $this->client->ltrim('foo', 1, -1));
        $this->assertEquals(array('B', 'C'), $this->client->lrange('foo', 0, -1));

        $this->assertEquals('OK', $this->client->ltrim('foo', 0, -2));
        $this->assertEquals(array('B'), $this->client->lrange('foo', 0, -1));
    }

    /**
     * @group redis-lists
     */
    public function testListRemove()
    {
        $this->assertSame(0, $this->client->lrem('foo', 0, 'A'));
        $this->client->rpush('foo', 'A', 'B', 'C', 'A', 'A');
        $this->assertSame(3, $this->client->lrem('foo', 0, 'A'));
        $this->assertEquals(array('B', 'C'), $this->client->lrange('foo', 0, -1));

        $this->client->rpush('foo', 'B');
        $this->assertSame(1, $this->client->lrem('foo', -1, 'B'));
        $this->assertEquals(array('B', 'C'), $this->client->lrange('foo', 0, -1));

        $this->client->rpush('foo', 'B');
        $this->assertSame(1, $this->client->lrem('foo', 1, 'B'));
        $this->assertEquals(array('C', 'B'), $this->client->lrange('foo', 0, -1));
    }

    /**
     * @group redis-lists
     */
    public function testListInsert()
    {
        $this->assertSame(0, $this->client->linsert('foo', 'after', 'hello', 'world'));
        $this->assertEquals(array(), $this->client->lrange('foo', 0, -1));

        $this->client->rpush('foo', 'hello', 'bar');
        $this->assertSame(-1, $this->client->linsert('foo', 'after', 'derp', 'asdf'));
        $this->assertEquals(array('hello', 'bar'), $this->client->lrange('foo', 0, -1));

        $this->assertSame(3, $this->client->linsert('foo', 'after', 'hello', 'world'));
        $this->assertEquals(array('hello', 'world', 'bar'), $this->client->lrange('foo', 0, -1));

        $this->assertSame(4, $this->client->linsert('foo', 'before', 'bar', 'baz'));
        $this->assertEquals(array('hello', 'world', 'baz', 'bar'), $this->client->lrange('foo', 0, -1));
    }

    /**
     * @group redis-lists
     */
    public function testListRightPopLeftPush()
    {
        $this->assertNull($this->client->rpoplpush('foo', 'bar'));
        $this->assertEquals(array(), $this->client->lrange('bar', 0, -1));

        $this->client->rpush('foo', 'A', 'B');
        $this->client->rpush('bar', 'C', 'D');
        $this->assertEquals('B', $this->client->rpoplpush('foo', 'bar'));
        $this->assertEquals(array('A'), $this->client->lrange('foo', 0, -1));
        $this->assertEquals(array('B', 'C', 'D'), $this->client->lrange('bar', 0, -1));
    }

    ///**
    // * @group redis-lists
    // */
    //public function testListBlockRightPopLeftPush()
    //{
    //    $this->markTestSkipped();
    //}

    //endregion

    //region Sets

    //public function testSetAdd()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSetRemove()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSetMove()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSetIsMember()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSetCard()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSetPop()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSetRandMember()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSetInter()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSetInterStore()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSetUnion()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSetUnionStore()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSetDiff()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSetDiffStore()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSetMembers()
    //{
    //    $this->markTestSkipped();
    //}

    //endregion

    //region Sorted Sets

    //public function testSortedSetAdd()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSortedSetRange()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSortedSetRemove()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSortedSetReverseRange()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSortedSetRangeByScore()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSortedSetReverseRangeByScore()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSortedSetCount()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSortedSetRemoveRangeByScore()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSortedSetRemoveRangeByRank()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSortedSetCard()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSortedSetScore()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSortedSetRank()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSortedSetReverseRank()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSortedSetIncrementBy()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSortedSetUnionStore()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSortedSetInterStore()
    //{
    //    $this->markTestSkipped();
    //}

    //endregion

    //region Server

    //public function testBackgroundRewriteAppendOnlyFile()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testBackgroundSave()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testConfig()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testDbSize()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testFlushAll()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testFlushDb()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testInfo()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testLastSave()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSave()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSlaveOf()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testTime()
    //{
    //    $this->markTestSkipped();
    //}

    //endregion

    //region Connection

    //public function testAuth()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testPing()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSelect()
    //{
    //    $this->markTestSkipped();
    //}

    //endregion

    //region Pub/Sub

    //public function testPsubscribe()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testSubscribe()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testPublish()
    //{
    //    $this->markTestSkipped();
    //}

    //endregion

    //region Transactions

    //public function testDiscard()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testExec()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testMulti()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testUnwatch()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testWatch()
    //{
    //    $this->markTestSkipped();
    //}

    //endregion

    //region Scripting

    //public function testEvalSha()
    //{
    //    $this->markTestSkipped();
    //}
    //
    //public function testScript()
    //{
    //    $this->markTestSkipped();
    //}

    //endregion
}

<?php

namespace Gmo\Common\Tests\Stream;

use Bolt\Collection\Bag;
use Gmo\Common\Stream\IteratorStream;
use PHPUnit\Framework\TestCase;

class IteratorStreamTest extends TestCase
{
    public function testReadAndTell()
    {
        $data = [
            '12345',
            '12345',
            '12345',
        ];

        $stream = new IteratorStream($data);

        $this->assertFalse($stream->eof());
        $this->assertSame('123', $stream->read(3)); // pump line and leave data remaining in buffer
        $this->assertSame(3, $stream->tell());
        $this->assertSame('45', $stream->read(2)); // only buffer needed
        $this->assertSame(5, $stream->tell());
        $this->assertSame('1234', $stream->read(4)); // pump line again
        $this->assertSame(9, $stream->tell());
        $this->assertSame('512', $stream->read(3)); // finish buffer and pump next line
        $this->assertSame(12, $stream->tell());
        $this->assertSame('345', $stream->read(10)); // read more than iterator/buffer has left
        $this->assertSame(15, $stream->tell());
        $this->assertTrue($stream->eof());
        $this->assertSame('', $stream->read(10)); // no more
        $this->assertSame(15, $stream->tell());
    }

    public function testInvalidIterator()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Iterable given to IteratorStream needs to yield strings. Got: integer');

        $stream = new IteratorStream([0]);
        $stream->read(1);
    }

    public function testIteratorToString()
    {
        $data = Bag::from([
            '12345',
            '12345',
            '12345',
        ]);
        $stream = new IteratorStream($data);
        $this->assertSame('123451234512345', (string) $stream);
    }

    public function testToStringDoesNotThrow()
    {
        $stream = new IteratorStream([0]);
        $this->assertSame('', (string) $stream);
    }

    public function testClose()
    {
        $stream = new IteratorStream(['12345']);

        $stream->close();
        $this->assertTrue($stream->eof());
        $this->assertEquals('', $stream->read(5));
    }

    public function testSize()
    {
        $stream = new IteratorStream([]);
        $this->assertNull($stream->getSize());

        $stream = new IteratorStream([], ['size' => 5]);
        $this->assertSame(5, $stream->getSize());
    }

    public function testIssers()
    {
        $stream = new IteratorStream([]);

        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isWritable());
        $this->assertTrue($stream->isReadable());
    }

    public function testSeek()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot seek a IteratorStream');

        $stream = new IteratorStream([]);

        $stream->seek(0);
    }

    public function testRewind()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot rewind a IteratorStream');

        $stream = new IteratorStream([]);

        $stream->rewind();
    }

    public function testWrite()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot write to a IteratorStream');

        $stream = new IteratorStream([]);

        $stream->write('');
    }

    public function testMetadata()
    {
        $stream = new IteratorStream([], ['metadata' => ['foo' => 'bar']]);
        $this->assertEquals(['foo' => 'bar'], $stream->getMetadata());
        $this->assertEquals('bar', $stream->getMetadata('foo'));
        $this->assertNull($stream->getMetadata('derp'));
    }
}

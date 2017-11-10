<?php

namespace Gmo\Common\Stream;

use Gmo\Common\Assert;
use Psr\Http\Message\StreamInterface;

/**
 * A stream that will iterate through an iterable.
 *
 * The iterable needs to yields strings.
 */
class IteratorStream implements StreamInterface
{
    /** @var \Iterator */
    private $it;
    /** @var int */
    private $size;
    /** @var array */
    private $metadata;
    /** @var int */
    private $tellPos = 0;
    /** @var string */
    private $buffer = '';

    /**
     * Constructor.
     *
     * @param iterable $iterable
     * @param array    $options
     */
    public function __construct(iterable $iterable, array $options = [])
    {
        $this->it = $iterable instanceof \Traversable
            ? new \IteratorIterator($iterable)
            : new \ArrayIterator($iterable);

        $this->it->rewind();

        $this->size = $options['size'] ?? null;
        $this->metadata = $options['metadata'] ?? [];
    }

    public function __toString(): string
    {
        try {
            return $this->getContents();
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function close(): void
    {
        $this->detach();
    }

    public function detach()
    {
        $this->tellPos = 0;
        $this->it = null;

        return null;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function tell(): int
    {
        return $this->tellPos;
    }

    public function eof(): bool
    {
        return !$this->it;
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        throw new \RuntimeException('Cannot seek a IteratorStream');
    }

    public function rewind(): void
    {
        throw new \RuntimeException('Cannot rewind a IteratorStream');
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function write($string): int
    {
        throw new \RuntimeException('Cannot write to a IteratorStream');
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function read($length): string
    {
        $data = substr($this->buffer, 0, $length);
        $readLen = strlen($data);
        $this->buffer = substr($this->buffer, $readLen);
        $this->tellPos += $readLen;
        $remaining = $length - $readLen;

        if ($remaining) {
            $this->pump($remaining);

            $remainingData = substr($this->buffer, 0, $remaining);
            $remainingDataLen = strlen($remainingData);
            $this->buffer = substr($this->buffer, $remainingDataLen);
            $data .= $remainingData;

            $this->tellPos += $remainingDataLen;
        }

        return $data;
    }

    public function getContents(): string
    {
        $result = '';
        while (!$this->eof()) {
            $result .= $this->read(1000000);
        }

        return $result;
    }

    public function getMetadata($key = null)
    {
        if (!$key) {
            return $this->metadata;
        }

        return $this->metadata[$key] ?? null;
    }

    private function pump(string $length): void
    {
        if (!$this->it) {
            return;
        }
        do {
            if (!$this->it->valid()) {
                $this->it = null;
                return;
            }
            $data = $this->it->current();
            $this->it->next();

            Assert::string($data, 'Iterable given to IteratorStream needs to yield strings. Got: %s');

            $this->buffer .= $data;
            $length -= strlen($data);
        } while ($length > 0);
    }
}

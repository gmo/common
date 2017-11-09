<?php

namespace Gmo\Common\Iterator;

use GuzzleHttp\Psr7\AppendStream;
use Http\Discovery\StreamFactoryDiscovery;
use Psr\Http\Message\StreamInterface;

/**
 * An iterator that will read a stream line by line.
 */
class LineIterator implements \IteratorAggregate
{
    /** @var StreamInterface */
    private $stream;
    /** @var int|null */
    private $maxLineLength;
    /** @var string|null */
    private $eol;

    /**
     * Constructor.
     *
     * @param StreamInterface $stream        Stream to read from
     * @param string|null     $eol           The EOL character or null to auto detect
     * @param int|null        $maxLineLength Maximum length of a line
     */
    public function __construct(StreamInterface $stream, string $eol = null, int $maxLineLength = null)
    {
        $this->stream = $stream;
        $this->maxLineLength = $maxLineLength;
        $this->eol = $eol;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Generator
    {
        if ($this->stream->tell() !== 0) {
            $this->stream->rewind();
        }

        if ($this->eol === null) {
            $this->detectLineBreak();
        }

        while (true) {
            $line = $this->readLine();

            if ($line === '' && $this->stream->eof()) {
                break;
            }

            yield $line;
        }
    }

    /**
     * Adapted from GuzzleHttp\Psr7\readline().
     * Modified to allow different EOL characters.
     *
     * @return string
     */
    private function readLine(): string
    {
        $buffer = '';
        $size = 0;

        // \r\n is two chars which requires slightly different logic.
        // Check this outside of the while loop to save an if statement for each character.
        if ($this->eol !== "\r\n") {
            while (!$this->stream->eof()) {
                // Using a loose equality here to match on '' and false.
                if (null == ($byte = $this->stream->read(1))) {
                    return $buffer;
                }
                $buffer .= $byte;

                // Break when a new line is found or the max length - 1 is reached
                if ($byte === $this->eol || ++$size === $this->maxLineLength - 1) {
                    break;
                }
            }
        } else {
            $prevByte = null;
            while (!$this->stream->eof()) {
                // Using a loose equality here to match on '' and false.
                if (null == ($byte = $this->stream->read(1))) {
                    return $buffer;
                }
                $buffer .= $byte;

                // Break when a new line is found or the max length - 2 is reached
                if ($prevByte . $byte === $this->eol || ++$size === $this->maxLineLength - 2) {
                    break;
                }

                $prevByte = $byte;
            }
        }

        return $buffer;
    }

    /**
     * Reads a 10k sample from the stream, the most occurring EOL character wins.
     */
    private function detectLineBreak(): void
    {
        $sample = $this->stream->read(10000);

        // Rewind stream if seekable, else decorate the stream so it doesn't have to be rewound.
        if ($this->stream->isSeekable()) {
            $this->stream->rewind();
        } else {
            $this->stream = new AppendStream([
                StreamFactoryDiscovery::find()->createStream($sample),
                $this->stream,
            ]);
        }

        // Adapted from https://stackoverflow.com/questions/11066857
        // Surrounded eols with spaces so they can be split like words
        $sample = preg_replace('/[^\r\n]*(\r\n|\n|\r)/','\1 ', $sample);
        // split words
        $parts = explode(' ', $sample);
        // count number of eol character occurrences
        $arr = array_count_values($parts);
        // choose one with most occurrences
        arsort($arr);
        $eol = key($arr);
        // ensure eol char is valid
        $eol = in_array($eol, ["\n", "\r", "\r\n"], true) ? $eol : "\n";

        $this->eol = $eol;
    }
}

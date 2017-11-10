<?php

namespace Gmo\Common\Csv;

use Generator;

/**
 * An iterator that parses CSV strings to Bags.
 */
class CsvParseIterator implements \IteratorAggregate
{
    use CsvControlTrait;

    /** @var iterable */
    private $inner;

    /**
     * Constructor.
     *
     * @param iterable|string[] $iterable An iterable that yields strings
     */
    public function __construct(iterable $iterable)
    {
        $this->inner = $iterable;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): Generator
    {
        foreach ($this->inner as $line) {
            // skip empty lines
            if (trim($line) === '') {
                continue;
            }

            yield Csv::parse($line, $this->delimiter, $this->enclosure, $this->escape);
        }
    }
}

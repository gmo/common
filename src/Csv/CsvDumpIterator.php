<?php

namespace Gmo\Common\Csv;

use Generator;

/**
 * An iterator that dumps CSV rows to strings.
 */
class CsvDumpIterator implements \IteratorAggregate
{
    use CsvControlTrait;

    /** @var iterable */
    private $inner;

    /**
     * Constructor.
     *
     * @param iterable|iterable[] $iterable An iterable that yields iterables
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
        foreach ($this->inner as $row) {
            yield Csv::dump($row, $this->delimiter, $this->enclosure, $this->escape);
        }
    }
}

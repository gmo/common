<?php

namespace Gmo\Common\Csv;

use Bolt\Collection\Bag;
use Generator;

/**
 * An iterator that associates the CSV headers with each row.
 */
class AssociativeCsvIterator implements \IteratorAggregate
{
    use CsvHeaderTrait;

    /** @var iterable */
    private $inner;

    /**
     * Constructor.
     *
     * @param iterable|array[] $iterable An iterable that yields arrays
     */
    public function __construct(iterable $iterable)
    {
        $this->inner = $iterable;
        $this->associativeRows = true;
        $this->skipFirstLine = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): Generator
    {
        foreach ($this->inner as $i => $row) {
            if ($this->headers === null) {
                $this->headers = $row;
            }
            if ($i === 0 && $this->skipFirstLine) {
                continue;
            }

            try {
                yield Bag::combine($this->headers, $row);
            } catch (\InvalidArgumentException $e) {
                $message = sprintf(
                    "Could not match CSV row #%d (with %d columns) to the headers (with %d columns) " .
                    "as they are not the same size.",
                    $i + 1,
                    count($row),
                    count($this->headers)
                );
                throw new \RuntimeException($message, 0, $e);
            }
        }
    }
}

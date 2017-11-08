<?php

namespace Gmo\Common\Csv;

use Bolt\Collection\Bag;

class AssociativeWriterDecorator implements CsvWriterInterface
{
    /** @var CsvWriterInterface */
    private $writer;
    /** @var Bag */
    private $headers;

    /**
     * Constructor.
     *
     * @param CsvWriterInterface $writer
     */
    public function __construct(CsvWriterInterface $writer)
    {
        $this->writer = $writer;
    }

    /**
     * Manually set the headers.
     *
     * @param string[]|iterable $headers
     */
    public function setHeaders(iterable $headers): void
    {
        $this->headers = Bag::from($headers);
    }

    /**
     * {@inheritdoc}
     */
    public function setCsvControl(string $delimiter = ',', string $enclosure = '"', string $escape = '\\'): void
    {
        $this->writer->setCsvControl($delimiter, $enclosure, $escape);
    }

    /**
     * Write a list of rows.
     *
     * @param iterable $rows
     */
    public function writeRows(iterable $rows): void
    {
        foreach ($rows as $row) {
            $this->writeRow($row);
        }
    }

    /**
     * Write a row.
     *
     * @param iterable $row
     */
    public function writeRow(iterable $row): void
    {
        // Convert to bag here in case $row cannot be rewound
        $row = Bag::from($row);

        if ($row->isIndexed()) {
            $this->writer->writeRow($row);

            return;
        }

        // If headers have not been set, use the keys of the given row
        if ($this->headers === null) {
            $this->headers = $row->keys();
        }

        $extra = $row->omit(...$this->headers);
        if (!$extra->isEmpty()) {
            throw new \RuntimeException(
                sprintf('Row contains extra fields not found in headers: "%s"', $extra->keys()->join('", "'))
            );
        }

        // Correctly sort values based on headers
        $row = $this->headers
            ->flip()
            ->replace($row)
            ->values()
        ;

        $this->writer->writeRow($row);
    }
}

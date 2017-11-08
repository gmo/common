<?php

namespace Gmo\Common\Csv;

interface CsvWriterInterface
{
    /**
     * Write a list of rows.
     *
     * @param iterable $rows
     */
    public function writeRows(iterable $rows): void;

    /**
     * Write a row.
     *
     * @param iterable $row
     */
    public function writeRow(iterable $row): void;

    /**
     * Set the CSV control characters.
     *
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     */
    public function setCsvControl(string $delimiter = ',', string $enclosure = '"', string $escape = '\\'): void;
}

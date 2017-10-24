<?php

namespace Gmo\Common;

use Bolt\Collection\Arr;
use Bolt\Collection\Bag;
use Bolt\Common\Ini;

/**
 * A CSV file abstraction.
 *
 * Main feature is associating row values with the header keys.
 * It also handles detecting line endings, validating csv controls, etc.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class CsvFile extends \SplFileObject
{
    /** @var bool */
    protected $reading;
    /** @var bool */
    protected $writing;
    /** @var bool */
    protected $associativeRows;
    /** @var bool */
    protected $skipFirstLine;
    /** @var Bag */
    protected $headers;

    /**
     * Constructor.
     *
     * @param string $fileName          The file to open
     * @param string $mode              The mode in which to open the file. See {@see fopen} for allowed modes.
     * @param bool   $associativeRows   Whether to read and write rows with their headers as keys
     * @param bool   $detectLineEndings Whether to automatically detect line endings
     *
     * @throws \RuntimeException When the filename cannot be opened
     * @throws \LogicException When the filename is a directory
     */
    public function __construct(
        string $fileName,
        string $mode = 'r',
        bool $associativeRows = true,
        bool $detectLineEndings = true
    ) {
        $this->associativeRows = $associativeRows;
        $this->skipFirstLine = $associativeRows;

        // Store whether the file pointer is opened for reading and/or writing
        // so we can throw better exception messages.
        $mode = rtrim($mode, 'tb');
        $this->reading = $mode === 'r' || strpos($mode, '+') !== false;
        $this->writing = $mode !== 'r';

        $mode .= 'b'; // force binary per fopen's instructions

        if ($detectLineEndings && !Ini::getBool('auto_detect_line_endings')) {
            // Detect line endings just when opening the file pointer as
            // that's all that's needed and won't have any side effects.
            Ini::set('auto_detect_line_endings', true);
            try {
                parent::__construct($fileName, $mode);
            } finally {
                Ini::set('auto_detect_line_endings', false);
            }
        } else {
            parent::__construct($fileName, $mode);
        }

        // Set CSV flags by default.
        $this->setFlags(static::READ_CSV | static::READ_AHEAD | static::SKIP_EMPTY);
    }

    /**
     * Returns the path to the file.
     *
     * @return string
     */
    public function __toString(): string
    {
        // Return the file path, not the current line.
        return \SplFileInfo::__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function setCsvControl($delimiter = ',', $enclosure = '"', $escape = '\\'): void
    {
        Assert::length($delimiter, 1, 'Delimiter must be a single character. Got: %s');
        Assert::length($enclosure, 1, 'Enclosure must be a single character. Got: %s');
        Assert::length($escape, 1, 'Escape must be a single character. Got: %s');

        parent::setCsvControl($delimiter, $enclosure, $escape);
    }

    /**
     * Manually set the headers for the file.
     *
     * @param string[]|iterable $headers
     * @param bool              $skipFirstLine Whether to skip the first line. If the file is missing headers,
     *                                         then this should be false. If the file has headers, that are just
     *                                         being renamed, then this should be true.
     */
    public function setHeaders(iterable $headers, bool $skipFirstLine): void
    {
        $this->headers = Bag::from($headers);
        $this->skipFirstLine = $skipFirstLine;
    }

    /**
     * Returns the headers (first line) of the file.
     *
     * @return Bag
     */
    public function getHeaders(): Bag
    {
        if ($this->headers === null) {
            if (($line = $this->key()) === 0) {
                $this->headers = $this->current();
            } else {
                parent::rewind();
                $this->headers = $this->current();
                $this->seek($line);
            }
        }

        return $this->headers;
    }

    /**
     * Grab the header row and skip first line if applicable.
     */
    public function rewind(): void
    {
        if (!$this->reading) {
            throw new \RuntimeException('The CSV file is not open for reading');
        }

        parent::rewind();

        if ($this->associativeRows) {
            $this->getHeaders();
        }
        if ($this->skipFirstLine) {
            parent::next();
        }
    }

    /**
     * Returns the current csv row and associates the headers if applicable.
     *
     * @return Bag
     */
    public function current(): Bag
    {
        $values = parent::current() ?: [];

        $values = Bag::from(array_map('trim', $values));

        if (!$this->associativeRows || $this->headers === null) {
            return $values;
        }

        try {
            return Bag::combine($this->headers, $values);
        } catch (\InvalidArgumentException $e) {
            $message = sprintf(
                "Could not match CSV row #%d (with %d columns) to the headers (with %d columns) " .
                "as they are not the same size.\nFile: %s",
                $this->key() + 1,
                $values->count(),
                $this->headers->count(),
                $this
            );
            throw new \RuntimeException($message, 0, $e);
        }
    }

    /**
     * Write a list of rows to the file.
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
     * Write a row to the file.
     *
     * Be warned that if associated rows is enabled and the file does not have headers, the headers need to be set
     * manually with {@see setHeaders}. Else the first values of the first row in the file will be used as the headers
     * and the row being written won't match.
     *
     * @param iterable $row
     */
    public function writeRow(iterable $row): void
    {
        if (!$this->writing) {
            throw new \RuntimeException('The CSV file is not open for writing');
        }

        // Convert to bag here in case $row cannot be rewound
        $row = Bag::from($row);

        if (!$this->associativeRows || $row->isIndexed()) {
            $this->doWriteRow($row);

            return;
        }

        // If headers have not been set and headers in file are empty, use the keys of the given row
        if ($this->headers === null && $this->getHeaders()->isEmpty()) {
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

        $this->doWriteRow($row);
    }

    private function doWriteRow(iterable $row): void
    {
        $result = $this->fputcsv(Arr::from($row));
        if ($result === 0 || $result === false) {
            throw new \RuntimeException(sprintf('Failed to write row to CSV file. File: %s', $this));
        }
    }
}

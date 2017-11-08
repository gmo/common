<?php

namespace Gmo\Common\Csv;

use Bolt\Collection\Arr;
use Bolt\Common\Thrower;
use Psr\Http\Message\StreamInterface;

class CsvStreamWriter implements CsvWriterInterface
{
    use CsvControlTrait;

    /** @var StreamInterface */
    private $stream;

    /**
     * Constructor.
     *
     * @param StreamInterface $stream The stream to write to
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Write a list of rows to the stream.
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
     * Write a row to the stream.
     *
     * @param iterable $row
     */
    public function writeRow(iterable $row): void
    {
        $str = $this->rowToCsvStr($row);

        try {
            $written = $this->stream->write($str);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to write CSV row.', 0, $e);
        }

        if (strlen($str) !== $written) {
            throw new \RuntimeException('Failed to write CSV row.');
        }
    }

    private function rowToCsvStr(iterable $row): string
    {
        $row = Arr::from($row);

        $res = fopen('php://temp', 'r+');

        try {
            $written = Thrower::call('fputcsv', $res, $row, $this->delimiter, $this->enclosure, $this->escape);
            $out = stream_get_contents($res, -1, 0);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to write CSV row.', 0, $e);
        } finally {
            fclose($res);
            unset($res);
        }

        if ($written === 0 || $written === false) {
            throw new \RuntimeException('Failed to write CSV row.');
        }

        return $out;
    }
}

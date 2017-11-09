<?php

namespace Gmo\Common\Csv;

use Bolt\Collection\Arr;
use Bolt\Collection\Bag;
use Bolt\Common\Thrower;

/**
 * Parse/Dump CSV lines.
 */
final class Csv
{
    /**
     * Parse a CSV line into a Bag.
     *
     * @param string $csv
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     *
     * @return Bag
     */
    public static function parse(
        string $csv,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escape = '\\'
    ): Bag {
        $row = str_getcsv($csv, $delimiter, $enclosure, $escape);

        return Bag::from(array_map('trim', $row));
    }

    /**
     * Dumps a row to a CSV line.
     *
     * @param iterable $row
     * @param string   $delimiter
     * @param string   $enclosure
     * @param string   $escape
     *
     * @return string
     */
    public static function dump(
        iterable $row,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escape = '\\'
    ): string {
        $row = Arr::from($row);

        $res = fopen('php://temp', 'r+');

        try {
            $written = Thrower::call('fputcsv', $res, $row, $delimiter, $enclosure, $escape);
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

    private function __construct()
    {
    }
}

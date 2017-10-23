<?php

namespace Gmo\Common\Tests;

use Bolt\Collection\Bag;
use Bolt\Common\Ini;
use Gmo\Common\CsvFile;
use PHPUnit\Framework\TestCase;

class CsvFileTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/fixtures/csv';
    private const CSV_EMPTY = self::FIXTURES . '/empty.csv';
    private const CSV_ONLY_HEADERS = self::FIXTURES . '/only-headers.csv';
    private const CSV_UNIX = self::FIXTURES . '/test-input.csv';
    private const CSV_TABS = self::FIXTURES . '/test-input.tabs.csv';
    private const CSV_MAC = self::FIXTURES . '/test-input.mac.csv';
    private const CSV_WIN = self::FIXTURES . '/test-input.win.csv';
    private const CSV_TOO_MANY_COLUMNS = self::FIXTURES . '/too-many-columns.csv';

    public function testSetCsvControl()
    {
        $csv = new CsvFile(static::CSV_UNIX);
        $controls = ["\t", '"', '\\'];
        $csv->setCsvControl(...$controls);
        $this->assertSame($controls, $csv->getCsvControl());
    }

    public function testSetCsvControlBadDelimiter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Delimiter must be a single character. Got: "as"');

        $csv = new CsvFile(static::CSV_UNIX);
        $csv->setCsvControl('as');
    }

    public function testSetCsvControlBadEnclosure()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Enclosure must be a single character. Got: "as"');

        $csv = new CsvFile(static::CSV_UNIX);
        $csv->setCsvControl(',', 'as');
    }

    public function testSetCsvControlBadEscape()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Escape must be a single character. Got: "as"');

        $csv = new CsvFile(static::CSV_UNIX);
        $csv->setCsvControl(',', '"', 'as');
    }

    public function testGetHeaders()
    {
        $expected = ['id', 'idAccount', 'date', 'totalFollowers', 'followers', 'totalStatuses', "statuses", 'score', 'timestamp'];

        $csv = new CsvFile(static::CSV_UNIX);
        $this->assertEquals($expected, $csv->getHeaders()->toArray());

        $csv = new CsvFile(static::CSV_UNIX);
        $csv->seek(2);
        $this->assertEquals($expected, $csv->getHeaders()->toArray(), 'should still work mid iteration');
        $this->assertSame(2, $csv->key(), 'should leave line pos the same as before');

        $csv = new CsvFile(static::CSV_ONLY_HEADERS);
        $this->assertEquals($expected, $csv->getHeaders()->toArray());

        $csv = new CsvFile(static::CSV_UNIX, 'r', false);
        $this->assertEquals($expected, $csv->getHeaders()->toArray());

        $csv = new CsvFile(static::CSV_ONLY_HEADERS, 'r', false);
        $this->assertEquals($expected, $csv->getHeaders()->toArray());
    }

    public function provideValidCsvFiles()
    {
        return [
            [static::CSV_UNIX, ','],
            [static::CSV_WIN, ','],
            [static::CSV_MAC, ','],
            [static::CSV_TABS, "\t"],
        ];
    }

    /**
     * @dataProvider provideValidCsvFiles
     *
     * @param string $file
     * @param string $delimiter
     */
    public function testIterationAssociativeRowsWithData(string $file, string $delimiter)
    {
        $csv = new CsvFile($file);
        $csv->setCsvControl($delimiter);

        $expected = [
            new Bag([
                'id'             => '1',
                'idAccount'      => '18',
                'date'           => '2012-03-20',
                'totalFollowers' => '36',
                'followers'      => '0',
                'totalStatuses'  => '83',
                'statuses'       => '0',
                'score'          => '12.55',
                'timestamp'      => '2012-02-20 08:34:22',
            ]),
            new Bag([
                'id'             => '2',
                'idAccount'      => '14',
                'date'           => '2012-03-20',
                'totalFollowers' => '5197',
                'followers'      => '0',
                'totalStatuses'  => '6054',
                'statuses'       => '0',
                'score'          => '\N',
                'timestamp'      => '2012-02-20 08:34:22',
            ]),
        ];

        $this->assertEquals($expected, iterator_to_array($csv, false));
        // Second time to ensure headers are skipped
        $this->assertEquals($expected, iterator_to_array($csv, false));
    }

    public function testIterationAssociativeRowsOnlyHeadersNoData()
    {
        $csv = new CsvFile(static::CSV_ONLY_HEADERS);

        $this->assertEmpty(iterator_to_array($csv, false));
    }

    public function testIterationAssociativeRowsEmpty()
    {
        $csv = new CsvFile(static::CSV_EMPTY);

        $this->assertEmpty(iterator_to_array($csv, false));
        $this->assertEmpty($csv->getHeaders());
    }

    public function testIterationAssociativeWithTooManyColumns()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            "Could not match CSV row #3 (with 10 columns) to the headers (with 9 columns) " .
            "as they are not the same size.\nFile: " . static::CSV_TOO_MANY_COLUMNS
        );

        $csv = new CsvFile(static::CSV_TOO_MANY_COLUMNS);

        iterator_to_array($csv, false);
    }

    /**
     * @dataProvider provideValidCsvFiles
     *
     * @param string $file
     * @param string $delimiter
     */
    public function testIterationWithData(string $file, string $delimiter)
    {
        $csv = new CsvFile($file, 'r', false);
        $csv->setCsvControl($delimiter);

        $expected = [
            new Bag([
                'id',
                'idAccount',
                'date',
                'totalFollowers',
                'followers',
                'totalStatuses',
                'statuses',
                'score',
                'timestamp',
            ]),
            new Bag([
                '1',
                '18',
                '2012-03-20',
                '36',
                '0',
                '83',
                '0',
                '12.55',
                '2012-02-20 08:34:22',
            ]),
            new Bag([
                '2',
                '14',
                '2012-03-20',
                '5197',
                '0',
                '6054',
                '0',
                '\N',
                '2012-02-20 08:34:22',
            ]),
        ];

        $this->assertEquals($expected, iterator_to_array($csv, false));
        // Second time to ensure headers are not skipped
        $this->assertEquals($expected, iterator_to_array($csv, false));
    }

    public function testIterationOnlyHeadersNoData()
    {
        $csv = new CsvFile(static::CSV_ONLY_HEADERS, 'r', false);

        $expected = [
            new Bag([
                'id',
                'idAccount',
                'date',
                'totalFollowers',
                'followers',
                'totalStatuses',
                'statuses',
                'score',
                'timestamp',
            ])
        ];

        $this->assertEquals($expected, iterator_to_array($csv, false));
    }

    public function testIterationEmpty()
    {
        $csv = new CsvFile(static::CSV_EMPTY, 'r', false);

        $this->assertEmpty(iterator_to_array($csv, false));
        $this->assertEmpty($csv->getHeaders());
    }

    public function testIterationWhenNotReading()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The CSV file is not open for reading');

        $csv = new CsvFile(static::CSV_ONLY_HEADERS, 'a');
        iterator_to_array($csv);
    }

    public function testDetectLineEndingsEnabled()
    {
        $eolKey = 'auto_detect_line_endings';
        $original = Ini::getBool($eolKey);
        Ini::set($eolKey, false);

        try {
            $csv1 = new CsvFile(static::CSV_MAC);
            $csv2 = new CsvFile(static::CSV_UNIX);

            $this->assertFalse(Ini::getBool($eolKey), 'ini value should not stay changed');

            $this->assertEquals(iterator_to_array($csv1), iterator_to_array($csv2));
        } finally {
            Ini::set($eolKey, $original);
        }
    }

    public function testDetectLineEndingsDisabled()
    {
        $eolKey = 'auto_detect_line_endings';
        $original = Ini::getBool($eolKey);
        Ini::set($eolKey, false);

        try {
            $csv1 = new CsvFile(static::CSV_MAC, 'r', true, false);
            $csv2 = new CsvFile(static::CSV_UNIX, 'r', true, false);

            $this->assertNotEquals(iterator_to_array($csv1), iterator_to_array($csv2));
        } finally {
            Ini::set($eolKey, $original);
        }
    }
}

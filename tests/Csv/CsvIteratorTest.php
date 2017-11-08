<?php

namespace Gmo\Common\Tests\Csv;

use Bolt\Collection\Bag;
use Gmo\Common\Csv\CsvIterator;
use Gmo\Common\Iterator\LineIterator;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class CsvIteratorTest extends TestCase
{
    public function testIteration()
    {
        $data = <<<CSV
"id","idAccount","date","totalFollowers","followers",totalStatuses,"statuses","score","timestamp"
"1","18","2012-03-20","36","0","83","0","12.55","2012-02-20 08:34:22"

"2","14","2012-03-20","5197","0","6054","0",\N,"2012-02-20 08:34:22"
CSV;

        $expected = [
            Bag::of('id', 'idAccount', 'date', 'totalFollowers', 'followers', 'totalStatuses', 'statuses', 'score', 'timestamp'),
            Bag::of('1', '18', '2012-03-20', '36', '0', '83', '0', '12.55', '2012-02-20 08:34:22'),
            Bag::of('2', '14', '2012-03-20', '5197', '0', '6054', '0', '\N', '2012-02-20 08:34:22'),
        ];

        $stream = stream_for($data);
        $it = new LineIterator($stream);
        $it = new CsvIterator($it);

        $this->assertEquals($expected, iterator_to_array($it));
        $this->assertEquals($expected, iterator_to_array($it));
    }

    public function testTabIteration()
    {
        $data = <<<CSV
"id"	"idAccount"	"date"	"totalFollowers"	"followers"	totalStatuses	"statuses"	"score"	"timestamp"
"1"	"18"	"2012-03-20"	"36"	"0"	"83"	"0"	"12.55"	"2012-02-20 08:34:22"
"2"	"14"	"2012-03-20"	"5197"	"0"	"6054"	"0"	\N	"2012-02-20 08:34:22"
CSV;

        $expected = [
            Bag::of('id', 'idAccount', 'date', 'totalFollowers', 'followers', 'totalStatuses', 'statuses', 'score', 'timestamp'),
            Bag::of('1', '18', '2012-03-20', '36', '0', '83', '0', '12.55', '2012-02-20 08:34:22'),
            Bag::of('2', '14', '2012-03-20', '5197', '0', '6054', '0', '\N', '2012-02-20 08:34:22'),
        ];

        $stream = stream_for($data);
        $it = new LineIterator($stream);
        $it = new CsvIterator($it);
        $it->setCsvControl("\t");

        $this->assertEquals($expected, iterator_to_array($it));
        $this->assertEquals($expected, iterator_to_array($it));
    }

    public function testSetCsvControlBadDelimiter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Delimiter must be a single character. Got: "as"');

        $it = new CsvIterator(new \EmptyIterator());
        $it->setCsvControl('as');
    }

    public function testSetCsvControlBadEnclosure()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Enclosure must be a single character. Got: "as"');

        $it = new CsvIterator(new \EmptyIterator());
        $it->setCsvControl(',', 'as');
    }

    public function testSetCsvControlBadEscape()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Escape must be a single character. Got: "as"');

        $it = new CsvIterator(new \EmptyIterator());
        $it->setCsvControl(',', '"', 'as');
    }
}

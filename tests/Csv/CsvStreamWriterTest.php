<?php

namespace Gmo\Common\Tests\Csv;

use Bolt\Collection\Bag;
use Gmo\Common\Csv\CsvStreamWriter;
use GuzzleHttp\Psr7\DroppingStream;
use function GuzzleHttp\Psr7\stream_for;
use PHPUnit\Framework\TestCase;

class CsvStreamWriterTest extends TestCase
{
    public function testWriteRows()
    {
        $stream = stream_for();

        $writer = new CsvStreamWriter($stream);

        $writer->writeRows(Bag::of(
            Bag::of('color1', 'color2', 'color3'),
            Bag::of('red', 'blue', 'green')
        ));

        $this->assertSame("color1,color2,color3\nred,blue,green\n", (string) $stream);
    }

    public function testWriteRowsTabs()
    {
        $stream = stream_for();

        $writer = new CsvStreamWriter($stream);
        $writer->setCsvControl("\t");

        $writer->writeRows(Bag::of(
            Bag::of('color1', 'color2', 'color3'),
            Bag::of('red', 'blue', 'green')
        ));

        $this->assertSame("color1\tcolor2\tcolor3\nred\tblue\tgreen\n", (string) $stream);
    }

    public function testUnwritableStream()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to write CSV row.');

        $stream = stream_for(fopen('php://temp', 'r'));
        $writer = new CsvStreamWriter($stream);
        $writer->writeRow([]);
    }

    public function testBadData()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to write CSV row.');

        $writer = new CsvStreamWriter(stream_for());
        $writer->writeRow([function() {}]);
    }

    public function testIncompleteWriteFails()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to write CSV row.');

        $stream = stream_for();
        $stream = new DroppingStream($stream, 5);

        $writer = new CsvStreamWriter($stream);
        $writer->writeRow(['red', 'blue']);
    }
}

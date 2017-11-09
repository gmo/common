<?php

namespace Gmo\Common\Tests\Csv;

use Bolt\Collection\Bag;
use Gmo\Common\Csv\AssociativeWriterDecorator;
use Gmo\Common\Csv\CsvWriterInterface;
use PHPUnit\Framework\TestCase;

class AssociativeWriterDecoratorTest extends TestCase
{
    public function testWriteRows()
    {
        $rawWriter = $this->prophesize(CsvWriterInterface::class);

        $writer = new AssociativeWriterDecorator($rawWriter->reveal());

        $rawWriter->writeRow(Bag::of('world', 'blue'))->shouldBeCalledTimes(1);
        $rawWriter->writeRow(Bag::of('world2', 'red'))->shouldBeCalledTimes(1);
        $rawWriter->writeRow(Bag::of('world3', 'green'))->shouldBeCalledTimes(1);

        $writer->writeRows([
            // These keys define the headers
            [
                'hello' => 'world',
                'color' => 'blue',
            ],
            // flipped values still work
            [
                'color' => 'red',
                'hello' => 'world2',
            ],
            // Indexed arrays still work and just ignore validation
            Bag::of('world3', 'green'),
        ]);
    }

    public function testWriteRowWithExtraFields()
    {
        $rawWriter = $this->prophesize(CsvWriterInterface::class);

        $writer = new AssociativeWriterDecorator($rawWriter->reveal());
        $writer->setHeaders(['hello']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Row contains extra fields not found in headers: "color", "derp"');

        $writer->writeRow([
            'hello' => 'world',
            'color' => 'blue',
            'derp'  => 'herp',
        ]);
    }

    public function testSetCsvControl()
    {
        $rawWriter = $this->prophesize(CsvWriterInterface::class);

        $writer = new AssociativeWriterDecorator($rawWriter->reveal());

        $rawWriter->setCsvControl("\t", '"', '\\')->shouldBeCalledTimes(1);

        $writer->setCsvControl("\t");
    }
}

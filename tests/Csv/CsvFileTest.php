<?php

namespace Gmo\Common\Tests\Csv;

use Bolt\Collection\Bag;
use Gmo\Common\Csv\CsvFile;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class CsvFileTest extends TestCase
{
    public function testCreation()
    {
        $file = vfsStream::setup()->url() . '/file.csv';
        $csv = new CsvFile($file);

        $this->assertFalse($csv->isReadable(), 'File does not need to exist');
    }

    public function testIteration()
    {
        $csv = new CsvFile(vfsStream::setup()->url() . '/file.csv');

        $data = <<<CSV
color,foo,hello
red,bar,world
blue,baz,world2
CSV;
        file_put_contents($csv, $data);

        $expected = [
            Bag::of('color', 'foo', 'hello'),
            Bag::of('red', 'bar', 'world'),
            Bag::of('blue', 'baz', 'world2'),
        ];

        $this->assertEquals($expected, iterator_to_array($csv));
        $this->assertEquals($expected, iterator_to_array($csv));
    }

    public function testIterationAssociative()
    {
        $csv = new CsvFile(vfsStream::setup()->url() . '/file.csv', true);

        $data = <<<CSV
color,foo,hello
red,bar,world
blue,baz,world2
CSV;
        file_put_contents($csv, $data);

        $expected = [
            Bag::from([
                'color' => 'red',
                'foo'   => 'bar',
                'hello' => 'world',
            ]),
            Bag::from([
                'color' => 'blue',
                'foo'   => 'baz',
                'hello' => 'world2',
            ]),
        ];

        $this->assertEquals($expected, iterator_to_array($csv));
        $this->assertEquals($expected, iterator_to_array($csv));
    }

    public function testIterationAssociativeManualHeaders()
    {
        $csv = new CsvFile(vfsStream::setup()->url() . '/file.csv');

        $data = <<<CSV
red,bar,world
blue,baz,world2
CSV;
        file_put_contents($csv, $data);

        $csv->setHeaders(['Color', 'Foo', 'Hello'], false);
        $expected = [
            Bag::from([
                'Color' => 'red',
                'Foo'   => 'bar',
                'Hello' => 'world',
            ]),
            Bag::from([
                'Color' => 'blue',
                'Foo'   => 'baz',
                'Hello' => 'world2',
            ]),
        ];

        $this->assertEquals($expected, iterator_to_array($csv));
        $this->assertEquals($expected, iterator_to_array($csv));
    }

    public function testOpenStreamError()
    {
        $csv = new CsvFile(vfsStream::setup()->url() . '/file.csv');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Unable to open $csv using mode \"r\"");

        $csv->openStream('r');
    }

    public function testCreateWriter()
    {
        $csv = new CsvFile(vfsStream::setup()->url() . '/file.csv');

        $csv->setHeaders(['color', 'foo', 'hello'], false);

        $data = [
            Bag::from([
                'color' => 'red',
                'foo'   => 'bar',
                'hello' => 'world',
            ]),
            Bag::from([
                'foo'   => 'baz',
                'color' => 'blue',
                'hello' => 'world2',
            ]),
        ];

        $csv->createWriter()->writeRow(['color', 'foo', 'hello']);
        // proves appending by creating new stream
        $csv->createWriter()->writeRows($data);

        $expected = <<<CSV
color,foo,hello
red,bar,world
blue,baz,world2

CSV;
        $this->assertEquals($expected, file_get_contents($csv));
    }
}

<?php

namespace Gmo\Common\Tests\Csv;

use Bolt\Collection\Bag;
use Gmo\Common\Csv\AssociativeCsvIterator;
use PHPUnit\Framework\TestCase;

class AssociativeCsvIteratorTest extends TestCase
{
    public function testIteration()
    {
        $data = [
            ['color', 'foo'],
            ['red', 'bar'],
            ['blue', 'baz'],
        ];

        $expected = [
            Bag::from([
                'color' => 'red',
                'foo'   => 'bar',
            ]),
            Bag::from([
                'color' => 'blue',
                'foo' => 'baz',
            ]),
        ];

        $it = new AssociativeCsvIterator($data);

        $this->assertEquals($expected, iterator_to_array($it));
        $this->assertEquals($expected, iterator_to_array($it));
    }

    public function testIterationChangeExistingHeaders()
    {
        $data = [
            ['color', 'foo'],
            ['red', 'bar'],
            ['blue', 'baz'],
        ];

        $expected = [
            Bag::from([
                'COLOR' => 'red',
                'FOO'   => 'bar',
            ]),
            Bag::from([
                'COLOR' => 'blue',
                'FOO' => 'baz',
            ]),
        ];

        $it = new AssociativeCsvIterator($data);
        $it->setHeaders(['COLOR', 'FOO'], true);

        $this->assertEquals($expected, iterator_to_array($it));
        $this->assertEquals($expected, iterator_to_array($it));
    }

    public function testIterationManualHeaders()
    {
        $data = [
            ['red', 'bar'],
            ['blue', 'baz'],
        ];

        $expected = [
            Bag::from([
                'color' => 'red',
                'foo'   => 'bar',
            ]),
            Bag::from([
                'color' => 'blue',
                'foo' => 'baz',
            ]),
        ];

        $it = new AssociativeCsvIterator($data);
        $it->setHeaders(['color', 'foo'], false);

        $this->assertEquals($expected, iterator_to_array($it));
        $this->assertEquals($expected, iterator_to_array($it));
    }

    public function testIterationInconsistentRows()
    {
        $data = [
            ['color', 'foo'],
            ['red', 'bar', 'wut'],
            ['blue', 'baz'],
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not match CSV row #2 (with 3 columns) to the headers (with 2 columns) as they are not the same size.');

        $it = new AssociativeCsvIterator($data);

        iterator_to_array($it);
    }
}

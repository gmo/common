<?php

namespace Gmo\Common\Tests\Iterator;

use Gmo\Common\Iterator\LineIterator;
use GuzzleHttp\Psr7\NoSeekStream;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class LineIteratorTest extends TestCase
{
    public function testIteration()
    {
        $data = <<<EOL
hello
world

world2

EOL;
        $stream = stream_for($data);

        $it = new LineIterator($stream);

        $expected = [
            "hello\n",
            "world\n",
            "\n",
            "world2\n",
        ];

        $this->assertSame($expected, iterator_to_array($it));
        $this->assertSame($expected, iterator_to_array($it));
    }

    public function testEmpty()
    {
        $data = <<<EOL
EOL;

        $stream = stream_for($data);
        $stream = new NoSeekStream($stream);

        $it = new LineIterator($stream);

        $this->assertSame([], iterator_to_array($it));
    }

    public function testEolWindows()
    {
        $data = "li\nne1\r\nli\rne2\r\n";

        $stream = stream_for($data);
        $stream = new NoSeekStream($stream);

        $it = new LineIterator($stream);

        $expected = [
            "li\nne1\r\n",
            "li\rne2\r\n",
        ];

        $this->assertSame($expected, iterator_to_array($it));
    }

    public function testEolMac()
    {
        $data = "li\nne1\rline2\r";

        $stream = stream_for($data);
        $stream = new NoSeekStream($stream);

        $it = new LineIterator($stream);

        $expected = [
            "li\nne1\r",
            "line2\r",
        ];

        $this->assertSame($expected, iterator_to_array($it));
    }
}

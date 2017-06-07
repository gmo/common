<?php

namespace Gmo\Common\Tests\Log;

use Gmo\Common\Log\SerializableFormatterWrapper;
use Gmo\Common\Serialization\AbstractSerializable;
use Monolog\Formatter\FormatterInterface;
use PHPUnit\Framework\TestCase;

class SerializableFormatterWrapperTest extends TestCase
{
    public function testDateTimeClassIsKept()
    {
        $formatter = $this->getFormatter();
        $record = $formatter->format(
            array(
                'time' => new \DateTime(),
            )
        );

        $this->assertTrue($record['time'] instanceof \DateTime);
    }

    public function testClassIsSerializedExceptDateTimeProperty()
    {
        $formatter = $this->getFormatter();

        $record = $formatter->format(
            array(
                'something' => new SomethingSerializable(new \DateTime()),
            )
        );

        $this->assertTrue(is_array($record['something']));
        $this->assertSame('Gmo\Common\Tests\Log\SomethingSerializable', $record['something']['class']);
        $this->assertTrue($record['something']['time'] instanceof \DateTime);
    }

    private function getFormatter()
    {
        return new SerializableFormatterWrapper(new NullFormatter());
    }
}

class NullFormatter implements FormatterInterface
{
    public function format(array $record)
    {
        return $record;
    }

    public function formatBatch(array $records)
    {
        return $records;
    }
}

class SomethingSerializable extends AbstractSerializable
{
    protected $time;

    public function __construct(\DateTime $time)
    {
        $this->time = $time;
    }
}

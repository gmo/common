<?php
namespace UnitTest;

use GMO\Common\AbstractSerializable;
use GMO\Common\DateTime;
use GMO\Common\Log\SerializableFormatterWrapper;
use Monolog\Formatter\FormatterInterface;

class SerializableFormatterWrapperTest extends \PHPUnit_Framework_TestCase {

	public function testDateTimeClassIsKept() {
		$formatter = $this->getFormatter();
		$record = $formatter->format(array(
			'time' => new DateTime(),
		));

		$this->assertTrue($record['time'] instanceof \DateTime);
	}

	public function testClassIsSerializedExceptDateTimeProperty() {
		$formatter = $this->getFormatter();

		$record = $formatter->format(array(
			'something' => new SomethingSerializable(new DateTime()),
		));

		$this->assertTrue(is_array($record['something']));
		$this->assertSame('UnitTest\SomethingSerializable', $record['something']['class']);
		$this->assertTrue($record['something']['time'] instanceof \DateTime);
	}

	private function getFormatter() {
		return new SerializableFormatterWrapper(new NullFormatter());
	}
}


class NullFormatter implements FormatterInterface {

	public function format(array $record) {
		return $record;
	}

	public function formatBatch(array $records) {
		return $records;
	}
}

class SomethingSerializable extends AbstractSerializable {

	public function __construct(\DateTime $time) {
		$this->time = $time;
	}

	protected $time;
}

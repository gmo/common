<?php
namespace UnitTest;

use Gmo\Common\String;

class StringTest extends \PHPUnit_Framework_TestCase {

	public function test_contains() {
		$this->assertTrue(String::contains("this", "t"));
		$this->assertTrue(String::contains("this", "h"));
		$this->assertTrue(String::contains("this", "i"));
		$this->assertTrue(String::contains("this", "s"));
		$this->assertTrue(String::contains("this", "th"));
		$this->assertTrue(String::contains("this", "is"));
		$this->assertTrue(String::contains("this", "this", false));

		$this->assertTrue(String::contains("thIs", "is", false));
		$this->assertTrue(String::contains("this", "THIS", false));
	}

	public function test_empty_returns_true() {
		$this->assertTrue(String::contains("this", ""));
		$this->assertTrue(String::contains("this", "", false));
		$this->assertTrue(String::startsWith("this", ""));
		$this->assertTrue(String::startsWith("this", "", false));
		$this->assertTrue(String::endsWith("this", ""));
		$this->assertTrue(String::endsWith("this", "", false));
	}

	public function test_does_not_contain() {
		$this->assertFalse(String::contains("this", "q"));
		$this->assertFalse(String::contains("this", "qthis"));
		$this->assertFalse(String::contains("this", "thisq"));
		$this->assertFalse(String::contains("this", "THIS"));
	}

	public function test_equals() {
		$this->assertTrue(String::equals("this", "this"));
		$this->assertFalse(String::equals("this", "THIS"));
		$this->assertTrue(String::equals("this", "THIS", false));
	}

	public function test_startsWith() {
		$this->assertTrue(String::startsWith("blah", "b"));
		$this->assertTrue(String::startsWith("blah", "bla"));
		$this->assertTrue(String::startsWith("blah", "blah"));

		$this->assertTrue(String::startsWith("bLAh", "blah", false));
	}

	public function test_does_not_startsWith() {
		$this->assertFalse(String::startsWith("blah", "q"));
		$this->assertFalse(String::startsWith("blah", "blaq"));
		$this->assertFalse(String::startsWith("blah", "qbla"));
		$this->assertFalse(String::startsWith("blah", "lah"));

		$this->assertFalse(String::startsWith("BLah", "lah"));
	}

	public function test_endsWith() {
		$this->assertTrue(String::endsWith("blah", "h"));
		$this->assertTrue(String::endsWith("blah", "ah"));
		$this->assertTrue(String::endsWith("blah", "blah"));

		$this->assertTrue(String::endsWith("blAH", "blah", false));
	}

	public function test_does_not_endsWith() {
		$this->assertFalse(String::endsWith("blah", "q"));
		$this->assertFalse(String::endsWith("blah", "qblah"));
		$this->assertFalse(String::endsWith("blah", "lalh"));
		$this->assertFalse(String::endsWith("blah", "lahq"));
		$this->assertFalse(String::endsWith("blah", "BLAH"));
	}

	public function test_split() {
		$this->assertSame(array("herp", "derp"), String::split("herp derp", " ")->toArray());
	}

	public function test_split_empty_returns_empty_collection() {
		$this->assertTrue(String::split("herp derp", "")->isEmpty());
	}

	public function test_split_first_last_empty_returns_false() {
		$this->assertFalse(String::splitFirst("herp derp", ""));
		$this->assertFalse(String::splitLast("herp derp", ""));
	}

	public function test_split_not_containing_delimiter() {
		$this->assertSame('herp derp', String::split("herp derp", ",")->first());
		$this->assertSame("herp derp", String::splitFirst("herp derp", ","));
		$this->assertSame("herp derp", String::splitLast("herp derp", ","));
	}

	public function test_split_first_containing_delimiter() {
		$this->assertSame("herp", String::splitFirst("herp derp", " "));
	}

	public function test_split_last_containing_delimiter() {
		$this->assertSame("derp", String::splitLast("herp derp", " "));
	}

	public function test_remove_not_containing_value() {
		$this->assertSame("asdf", String::removeFirst("asdf", "zxc"));
		$this->assertSame("asdf", String::removeLast("asdf", "zxc"));
	}

	public function test_remove_first_containing_value() {
		$this->assertSame("HelloHelloGoodbye", String::removeFirst("HelloGoodbyeHelloGoodbye", "Goodbye"));
		$this->assertSame("HelloHelloGoodbye", String::removeFirst("HelloGOODBYEHelloGoodbye", "goodbye", false));
	}

	public function test_remove_last_containing_value() {
		$this->assertSame("HelloGoodbyeGoodbye", String::removeLast("HelloGoodbyeHelloGoodbye", "Hello"));
		$this->assertSame("HelloGoodbyeGoodbye", String::removeLast("HelloGoodbyeHELLOGoodbye", "hello", false));
	}

	public function test_class_name() {
		$this->assertSame('StringTest', String::className($this));
		$this->assertSame('StringTest', String::className('UnitTest\\StringTest'));
	}

	public function test_class_name_does_not_exist() {
		$this->assertFalse(String::className('ClassDoesNotExist'));
	}

}

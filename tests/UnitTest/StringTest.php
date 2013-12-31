<?php
namespace UnitTest;

use GMO\Common\String;

class StringTest extends \PHPUnit_Framework_TestCase {

	public function test_contains() {
		$this->assertTrue(String::contains("this", "t"));
		$this->assertTrue(String::contains("this", "h"));
		$this->assertTrue(String::contains("this", "i"));
		$this->assertTrue(String::contains("this", "s"));
		$this->assertTrue(String::contains("this", "th"));
		$this->assertTrue(String::contains("this", "is"));
		$this->assertTrue(String::containsInsensitive("this", "this"));

		$this->assertTrue(String::containsInsensitive("thIs", "is"));
		$this->assertTrue(String::containsInsensitive("this", "THIS"));
	}

	public function test_empty_returns_true() {
		$this->assertTrue(String::contains("this", ""));
		$this->assertTrue(String::containsInsensitive("this", ""));
		$this->assertTrue(String::startsWith("this", ""));
		$this->assertTrue(String::startsWithInsensitive("this", ""));
		$this->assertTrue(String::endsWith("this", ""));
		$this->assertTrue(String::endsWithInsensitive("this", ""));
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

		$this->assertTrue(String::startsWithInsensitive("bLAh", "blah"));
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

		$this->assertTrue(String::endsWithInsensitive("blAH", "blah"));
	}

	public function test_does_not_endsWith() {
		$this->assertFalse(String::endsWith("blah", "q"));
		$this->assertFalse(String::endsWith("blah", "qblah"));
		$this->assertFalse(String::endsWith("blah", "lalh"));
		$this->assertFalse(String::endsWith("blah", "lahq"));
		$this->assertFalse(String::endsWith("blah", "BLAH"));
	}

	public function test_split_empty_returns_false() {
		$this->assertFalse(String::splitFirst("herp derp", ""));
		$this->assertFalse(String::splitLast("herp derp", ""));
	}

	public function test_split_not_containing_delimiter() {
		$this->assertSame("herp derp", String::splitFirst("herp derp", ","));
		$this->assertSame("herp derp", String::splitLast("herp derp", ","));
	}

	public function test_split_first_containing_delimiter() {
		$this->assertSame("herp", String::splitFirst("herp derp", " "));
	}

	public function test_split_last_containing_delimiter() {
		$this->assertSame("derp", String::splitLast("herp derp", " "));
	}

}
 
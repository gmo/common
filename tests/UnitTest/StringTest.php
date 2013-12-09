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
	}

	public function test_empty_returns_true() {
		$this->assertTrue(String::contains("this", ""));
		$this->assertTrue(String::startsWith("this", ""));
		$this->assertTrue(String::endsWith("this", ""));
	}

	public function test_does_not_contain() {
		$this->assertFalse(String::contains("this", "q"));
		$this->assertFalse(String::contains("this", "qthis"));
		$this->assertFalse(String::contains("this", "thisq"));
	}

	public function test_startsWith() {
		$this->assertTrue(String::startsWith("blah", "b"));
		$this->assertTrue(String::startsWith("blah", "bla"));
		$this->assertTrue(String::startsWith("blah", "blah"));
	}

	public function test_does_not_startsWith() {
		$this->assertFalse(String::startsWith("blah", "q"));
		$this->assertFalse(String::startsWith("blah", "blaq"));
		$this->assertFalse(String::startsWith("blah", "qbla"));
		$this->assertFalse(String::startsWith("blah", "lah"));
	}

	public function test_endsWith() {
		$this->assertTrue(String::endsWith("blah", "h"));
		$this->assertTrue(String::endsWith("blah", "ah"));
		$this->assertTrue(String::endsWith("blah", "blah"));
	}

	public function test_does_not_endsWith() {
		$this->assertFalse(String::endsWith("blah", "q"));
		$this->assertFalse(String::endsWith("blah", "qblah"));
		$this->assertFalse(String::endsWith("blah", "lalh"));
		$this->assertFalse(String::endsWith("blah", "lahq"));
	}

}
 
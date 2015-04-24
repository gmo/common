<?php
namespace Gmo\Common\UnitTest;

use Gmo\Common\Path;

class PathTest extends \PHPUnit_Framework_TestCase {

	public function test_path_is_absolute() {
		$this->assertTrue(Path::isAbsolute("/var/"));
		$this->assertTrue(Path::isAbsolute("/var"));
		$this->assertTrue(Path::isAbsolute("/var/blah.json"));
	}

	public function test_path_is_relative() {
		$this->assertFalse(Path::isAbsolute("./"));
		$this->assertFalse(Path::isAbsolute("/../"));
		$this->assertFalse(Path::isAbsolute("/.."));
		$this->assertFalse(Path::isAbsolute("../"));
	}

	public function test_true_path() {
		$this->assertEquals("/herp/derp/dummy.txt", Path::truePath("dummy.txt", "/herp/derp"));
		$this->assertEquals("herp/dummy.txt", Path::truePath("../dummy.txt", "herp/derp"));
		$this->assertEquals("herp/dummy.txt", Path::truePath("/../dummy.txt", "herp/derp/"));
		$this->assertEquals("dummy.txt", Path::truePath("../../dummy.txt", "herp/derp"));
	}

	/**
	 * @expectedException \GMO\Common\Exception\PathException
	 * @expectedExceptionMessage Cannot move up another directory
	 */
	public function test_true_path_exception() {
		$this->assertEquals("dummy.txt", Path::truePath("../../dummy.txt", "herp"));
	}
}

<?php
namespace UnitTest;

use GMO\Common\Path;

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

	public function test_absolute_path_to_absolute_file() {
		$file = "/var/www/html/index.html";
		$this->assertEquals($file, Path::toAbsFile(null, $file));
	}

	public function test_absolute_path_to_absolute_dir() {
		$file = "/var/www/html/";
		$this->assertEquals($file, Path::toAbsDir(null, $file));
	}

	public function test_relative_dir_to_absolute_dir() {
		$this->assertEquals("/usr", Path::toAbsDir("/usr/bin", "../"));
		$this->assertEquals("/usr", Path::toAbsDir("/usr/bin/", "../"));
		$this->assertEquals("/usr", Path::toAbsDir("/usr/bin/", "/../"));
		$this->assertEquals("/usr", Path::toAbsDir("/usr/bin/", "/.."));
		$this->assertEquals("/usr/bin", Path::toAbsDir("/usr/bin/", "./"));
		$this->assertEquals("/usr/bin", Path::toAbsDir("/usr/bin/", "./."));
	}

	public function test_relative_file_to_absolute_file() {
		$this->assertEquals("/usr/bin/dummy.txt", Path::toAbsFile("/usr/bin", "dummy.txt"));
		$this->assertEquals("/usr/bin/dummy.txt", Path::toAbsFile("/usr/bin/", "dummy.txt"));
		$this->assertEquals("/usr/bin/dummy.txt", Path::toAbsFile("/usr/bin/", "./dummy.txt"));
		$this->assertEquals("/usr/dummy.txt", Path::toAbsFile("/usr/bin", "../dummy.txt"));
		$this->assertEquals("/usr/dummy.txt", Path::toAbsFile("/usr/bin", "/../dummy.txt"));
		$this->assertEquals("/usr/dummy.txt", Path::toAbsFile("/usr/bin/", "../dummy.txt"));
		$this->assertEquals("/usr/dummy.txt", Path::toAbsFile("/usr/bin/", "/../dummy.txt"));
	}

}
 
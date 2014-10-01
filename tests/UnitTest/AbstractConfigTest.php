<?php
namespace UnitTest;

use GMO\Common\AbstractConfig;
use GMO\Common\Collections\ArrayCollection;
use GMO\Common\Path;

class AbstractConfigTest extends \PHPUnit_Framework_TestCase {

	public function test_get_json_value() {
		$this->assertEquals("git", JsonConfig::getRepoType());
	}

	public function test_get_ini_value() {
		$this->assertEquals(1, IniConfig::getAllowedAuthorization());
	}

	public function test_get_abs_path_from_config() {
		$expected = Path::truePath("../testConfig.yml", __DIR__);
		$this->assertEquals($expected, IniConfig::getYamlFile());
	}

	public function test_get_path_default_value() {
		$this->assertEquals("nope", IniConfig::getUnknownFile());
	}

	public function test_get_default_value() {
		$this->assertSame("defaultValue", IniConfig::getDefaultKey());
	}

	public function test_get_bool_string_true() {
		$this->assertTrue(IniConfig::getBool("SWITCHES", "debug"));
	}

	public function test_get_bool_string_false() {
		$this->assertFalse(IniConfig::getBool("SWITCHES", "other"));
	}

	public function test_get_bool_string_other() {
		$this->assertFalse(IniConfig::getBool("SWITCHES", "unknown"));
	}

	public function test_get_bool() {
		$this->assertTrue(IniConfig::getBool("SWITCHES", "live"));
		$this->assertFalse(IniConfig::getBool("SWITCHES", "nope"));
	}

	public function test_get_bool_int() {
		$this->assertTrue(IniConfig::getBool("SWITCHES", "live"));
		$this->assertFalse(IniConfig::getBool("SWITCHES", "nope"));
	}

	public function test_get_list() {
		$list = IniConfig::getList("LISTS", "life");
		$this->assertTrue($list instanceof ArrayCollection);
		$this->assertCount(4, $list);
	}

	public function test_get_list_default_value_array() {
		$default = array('hi');
		$list = IniConfig::getList("LISTS", "adsf", $default);
		$this->assertTrue($list instanceof ArrayCollection);
		$this->assertCount(1, $list);
		$this->assertSame('hi', $list->first());
	}

	public function test_get_list_default_value_array_collection() {
		$default = new ArrayCollection(array('hi'));
		$list = IniConfig::getList("LISTS", "adsf", $default);
		$this->assertTrue($list instanceof ArrayCollection);
		$this->assertCount(1, $list);
		$this->assertSame('hi', $list->first());
	}

	public function test_get_list_default_value_traversable() {
		$default = new \ArrayObject(array('hi'));
		$list = IniConfig::getList("LISTS", "adsf", $default);
		$this->assertTrue($list instanceof ArrayCollection);
		$this->assertCount(1, $list);
		$this->assertSame('hi', $list->first());
	}

	/**
	 * @expectedException \GMO\Common\Exception\ConfigException
	 * @expectedExceptionMessage Config file key: "listssss" is missing!
	 */
	public function test_get_nonexistent_list() {
		IniConfig::getList("LISTS", "listssss");
	}

	/**
	 * @expectedException \GMO\Common\Exception\ConfigException
	 * @expectedExceptionMessage Config file key: "asdf" is missing!
	 */
	public function test_missing_key() {
		IniConfig::getMissingKey();
	}

	/**
	 * @expectedException \GMO\Common\Exception\ConfigException
	 * @expectedExceptionMessage Config file doesn't exist
	 */
	public function test_nonexistent_config_file() {
		NonexistentConfig::getSomething();
	}

	/**
	 * @expectedException \GMO\Common\Exception\ConfigException
	 * @expectedExceptionMessage Unknown config file format
	 */
	public function test_unknown_config_format() {
		YamlConfig::getSomething();
	}

}

#region Test Config Classes
class JsonConfig extends AbstractConfig {

	/**
	 * Absolute directory path to project root or relative this directory
	 * @return string
	 */
	public static function getProjectRootDir() { return "../.."; }
	/**
	 * Config ini/json absolute file path or relative to project root
	 * @return string
	 */
	public static function getConfigFile() { return "package.json"; }

	public static function getRepoType() {
		return static::getValue("repository", "type");
	}

}

class IniConfig extends AbstractConfig {

	/**
	 * Absolute directory path to project root or relative this directory
	 * @return string
	 */
	public static function getProjectRootDir() { return "../.."; }

	/**
	 * Config ini/json absolute file path or relative to project root
	 * @return string
	 */
	public static function getConfigFile() { return "tests/testConfig.ini"; }

	public static function getAllowedAuthorization() {
		return static::getValue("AUTHORIZATION", "allow");
	}
	public static function getYamlFile() {
		return static::getPath("FILES", "yaml");
	}
	public static function getUnknownFile() {
		return static::getPath("FILES", "derp", "nope");
	}
	public static function getDefaultKey() {
		return static::getValue("NOT", "needed", "defaultValue");
	}
	public static function getMissingKey() {
		return static::getValue("NEEDED", "asdf");
	}

	public static function getList($section, $key, $default = null) {
		return parent::getList($section, $key, $default);
	}

	public static function getBool($section, $key, $default = null) {
		return parent::getBool($section, $key, $default);
	}

}

class NonexistentConfig extends AbstractConfig {

	/**
	 * Absolute directory path to project root or relative this directory
	 * @return string
	 */
	public static function getProjectRootDir() { return "../.."; }

	/**
	 * Config ini/json absolute file path or relative to project root
	 * @return string
	 */
	public static function getConfigFile() { return "asdf"; }

	public static function getSomething() {
		return static::getValue("nope", "nope");
	}
}

class YamlConfig extends AbstractConfig {

	/**
	 * Absolute directory path to project root or relative this directory
	 * @return string
	 */
	public static function getProjectRootDir() { return "../.."; }

	/**
	 * Config ini/json absolute file path or relative to project root
	 * @return string
	 */
	public static function getConfigFile() { return "tests/testConfig.yml"; }

	public static function getSomething() {
		return static::getValue("nope", "nope");
	}
}
#endregion

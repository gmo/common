<?php
namespace Gmo\Common\Tests\Config;

use GMO\Common\Collections\ArrayCollection;
use GMO\Common\Config\AbstractConfig;

class AbstractConfigTest extends \PHPUnit_Framework_TestCase {

	public function test_get_json_value() {
		$this->assertEquals("git", JsonConfig::getRepoType());
	}

	public function test_get_ini_value() {
		$this->assertEquals(1, IniConfig::getAllowedAuthorization());
		$this->assertSame("dev", IniConfig::getValue(null, 'env'));
	}

	public function test_get_abs_path_from_config() {
		$expected = realpath(__DIR__ . '/../fixtures/config/testConfig.yml');
		$this->assertEquals($expected, IniConfig::getYamlFile());
	}

	public function test_get_path_default_value() {
		$this->assertEquals("nope", IniConfig::getUnknownFile());
	}

	public function test_allow_empty_value() {
		$this->assertSame("", IniConfig::getValue(null, "password", null, true));
		$this->assertSame("", JsonConfig::getValue(null, "homepage", "", true));
		$this->assertSame(null, JsonConfig::getValue(null, "bugs", null, true));
	}

	public function test_get_default_value() {
		$this->assertSame("defaultValue", IniConfig::getDefaultKey());
		$this->assertSame("defaultValue", IniConfig::getValue(null, "needed", "defaultValue"));
		$this->assertSame("defaultValue", IniConfig::getValue(null, "password", "defaultValue", true));
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
	 * @expectedExceptionMessage Config key: "listssss" is missing!
	 */
	public function test_get_nonexistent_list() {
		IniConfig::getList("LISTS", "listssss");
	}

	/**
	 * @expectedException \GMO\Common\Exception\ConfigException
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
		CsvConfig::getSomething();
	}

}

#region Test Config Classes
class JsonConfig extends AbstractConfig {

	public static function setProjectDir() { return ".."; }
	public static function setConfigFile() { return "fixtures/config/package.json"; }

	public static function getRepoType() {
		return static::getValue("repository", "type");
	}
}

class IniConfig extends AbstractConfig {

	public static function setProjectDir() { return ".."; }
	public static function setConfigFile() { return "fixtures/config/testConfig.ini"; }

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
}

class NonexistentConfig extends AbstractConfig {

	public static function setProjectDir() { return ".."; }
	public static function setConfigFile() { return "asdf"; }

	public static function getSomething() {
		return static::getValue("nope", "nope");
	}
}

class CsvConfig extends AbstractConfig {

	public static function setProjectDir() { return ".."; }
	public static function setConfigFile() { return "fixtures/config/testConfig.csv"; }

	public static function getSomething() {
		return static::getValue("nope", "nope");
	}
}
#endregion

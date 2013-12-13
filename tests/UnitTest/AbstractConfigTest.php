<?php
namespace UnitTest;

use GMO\Common\AbstractConfig;

class AbstractConfigTest extends \PHPUnit_Framework_TestCase {

	public function test_get_json_value() {
		$this->assertEquals("git", JsonConfig::getRepoType());
	}

	public function test_get_ini_value() {
		$this->assertEquals(1, IniConfig::getAllowedAuthorization());
	}

	public function test_get_abs_path_from_config() {
		$projectRoot = realpath(__DIR__ . "/../..");
		$expected = $projectRoot . "/tests/testConfig.yml";
		$this->assertEquals($expected, IniConfig::getYamlFile());
	}

	public function test_get_optional_value() {
		$this->assertFalse(IniConfig::getOptionalKey());
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
		return static::toAbsPathFromProjectRoot(static::getValue("FILES", "yaml"));
	}
	public static function getOptionalKey() {
		return static::getValue("NOT", "needed", true);
	}
	public static function getMissingKey() {
		return static::getValue("NEEDED", "asdf");
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
<?php
namespace Gmo\Common\UnitTest\Config;

use Gmo\Common\Config\EnvironmentAwareConfig;

class EnvironmentAwareConfigTest extends \PHPUnit_Framework_TestCase {

	public function testDefaultEnvironment() {
		TestConfig::setEnvironment('production');
		$foo = TestConfig::getValue('test', 'foo');
		$this->assertSame('bar', $foo);
	}

	public function testSpecificEnvironment() {
		TestConfig::setEnvironment('production');
		$value = TestConfig::getValue('test', 'hello');
		$this->assertSame('world2', $value);
	}

	public function testUnknownEnvironmentUsesDefault() {
		TestConfig::setEnvironment('asdf');
		$value = TestConfig::getValue('test', 'hello');
		$this->assertSame('world', $value);
	}

	public function testGettingValueFromAliasedEnvironment() {
		TestConfig::setEnvironment('development');
		$value = TestConfig::getValue('test', 'hello');
		$this->assertSame('world2', $value);
	}

	/**
	 * @expectedException \GMO\Common\Exception\ConfigException
	 */
	public function testUnknownAliasedEnvironment() {
		TestConfig::setEnvironment('development');
		TestConfig::getValue('test', 'error');
	}

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$originalEnvironment = getenv('PHP_ENV');
	}

	public static function tearDownAfterClass() {
		putenv('PHP_ENV=' . static::$originalEnvironment);
		parent::tearDownAfterClass();
	}

	static $originalEnvironment;
}


class TestConfig extends EnvironmentAwareConfig {

	public static function setEnvironment($env) {
		putenv("PHP_ENV=$env");
		static::doSetConfig();
	}

	public static function setProjectDir() { return "../../.."; }
	public static function setConfigFile() { return "tests/testConfig.yml"; }
}

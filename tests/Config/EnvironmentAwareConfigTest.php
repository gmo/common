<?php
namespace Gmo\Common\Tests\Config;

use GMO\Common\Config\EnvironmentAwareConfig;
use PHPUnit\Framework\TestCase;

class EnvironmentAwareConfigTest extends TestCase {

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

	public function testEnvironmentExtendsParent() {
		TestConfig::setEnvironment('development');
		$value = TestConfig::getValue('test', 'key');
		$this->assertSame('staging_key', $value);
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

	public function testSpecificEnvironmentFiles() {
		TestConfig::setEnvironment('development');
		$value = TestConfig::getValue(null, 'password');
		$this->assertSame('password1', $value);
	}
}


class TestConfig extends EnvironmentAwareConfig {

	protected static $env;

	public static function getEnvironment() {
		return static::$env;
	}

	public static function setEnvironment($env) {
		static::$env = $env;
		static::doSetConfig();
	}

	public static function setProjectDir() { return ".."; }
	public static function setConfigFile() { return "fixtures/config/testConfig.yml"; }
}

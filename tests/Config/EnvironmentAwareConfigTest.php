<?php

namespace Gmo\Common\Tests\Config;

use Gmo\Common\Tests\Config\Singleton\EnvConfig as TestConfig;
use PHPUnit\Framework\TestCase;

/**
 * @group legacy
 */
class EnvironmentAwareConfigTest extends TestCase
{
    public function testDefaultEnvironment()
    {
        TestConfig::setEnvironment('production');
        $foo = TestConfig::getValue('test', 'foo');
        $this->assertSame('bar', $foo);
    }

    public function testSpecificEnvironment()
    {
        TestConfig::setEnvironment('production');
        $value = TestConfig::getValue('test', 'hello');
        $this->assertSame('world2', $value);
    }

    public function testEnvironmentExtendsParent()
    {
        TestConfig::setEnvironment('development');
        $value = TestConfig::getValue('test', 'key');
        $this->assertSame('staging_key', $value);
    }

    public function testUnknownEnvironmentUsesDefault()
    {
        TestConfig::setEnvironment('asdf');
        $value = TestConfig::getValue('test', 'hello');
        $this->assertSame('world', $value);
    }

    public function testGettingValueFromAliasedEnvironment()
    {
        TestConfig::setEnvironment('development');
        $value = TestConfig::getValue('test', 'hello');
        $this->assertSame('world2', $value);
    }

    /**
     * @expectedException \GMO\Common\Exception\ConfigException
     */
    public function testUnknownAliasedEnvironment()
    {
        TestConfig::setEnvironment('development');
        TestConfig::getValue('test', 'error');
    }

    public function testSpecificEnvironmentFiles()
    {
        TestConfig::setEnvironment('development');
        $value = TestConfig::getValue(null, 'password');
        $this->assertSame('password1', $value);
    }
}
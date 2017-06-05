<?php

namespace Gmo\Common\Tests\Config;

use Gmo\Common\Config\Config;
use Gmo\Common\Tests\Config\Singleton\TestConfig;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Gmo\Common\Config\Config
 */
class ConfigTest extends TestCase
{
    public function testGet()
    {
        $this->assertSame('bar', TestConfig::get('test/foo'));
        $this->assertSame('world2', TestConfig::get('test/hello'));
    }

    public function testAbsPath()
    {
        $expected = realpath(__DIR__ . '/../../composer.json');
        $actual = TestConfig::absPath('../composer.json');
        $this->assertEquals($expected, $actual);
    }

    public function testEnv()
    {
        TestConfig::setEnv('default');
        $this->assertFalse(TestConfig::isProduction());
        $this->assertFalse(TestConfig::isStaging());
        $this->assertFalse(TestConfig::isDevelopment());

        TestConfig::setEnv('production');
        $this->assertTrue(TestConfig::isProduction());
        $this->assertFalse(TestConfig::isStaging());
        $this->assertFalse(TestConfig::isDevelopment());

        TestConfig::setEnv('staging');
        $this->assertFalse(TestConfig::isProduction());
        $this->assertTrue(TestConfig::isStaging());
        $this->assertFalse(TestConfig::isDevelopment());

        TestConfig::setEnv('development');
        $this->assertFalse(TestConfig::isProduction());
        $this->assertFalse(TestConfig::isStaging());
        $this->assertTrue(TestConfig::isDevelopment());
    }

    public function testCI()
    {
        $expected = (bool) getenv('CI');
        $this->assertEquals($expected, TestConfig::isCI());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Gmo\Common\Config\Config cannot be used directly. Please subclass in your project.
     */
    public function testSelfAccess()
    {
        Config::get('derp');
    }
}

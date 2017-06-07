<?php

namespace Gmo\Common\Tests\Config;

use Gmo\Common\Config\ConfigBag;
use Gmo\Common\Config\ConfigFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Gmo\Common\Config\ConfigFactory
 */
class ConfigFactoryTest extends TestCase
{
    /** @var ConfigBag */
    private $config;

    public function setUp()
    {
        $this->config = (new ConfigFactory())->create(__DIR__ . '/../', 'fixtures/config/testConfig.yml');
        $this->config->setEnv('production');
    }

    public function testCreate()
    {
        $this->assertInstanceOf(ConfigBag::class, $this->config);
    }

    public function testDefaultEnvironmentMerged()
    {
        $this->assertSame('bar', $this->config->get('test/foo'));
    }

    public function testSpecificEnvironment()
    {
        $this->assertSame('world2', $this->config->get('test/hello'));
    }

    public function testEnvironmentExtendsParent()
    {
        $this->assertSame('staging_key', $this->config->withEnv('development')->get('test/key'));
    }

    public function testExternalEnvironmentFiles()
    {
        $this->assertSame('password1', $this->config->withEnv('development')->get('password'));
    }

    /**
     * @expectedException \GMO\Common\Exception\ConfigException
     * @expectedExceptionMessage Config file doesn't exist.
     */
    public function testNonExistentFile()
    {
        (new ConfigFactory())->create(__DIR__, 'nope');
    }

    /**
     * @expectedException \Gmo\Common\Exception\Dependency\CyclicDependencyException
     * @expectedExceptionMessage The environments 'staging', 'development' have a cyclic dependency.
     */
    public function testInvalidEnvironmentDefinition()
    {
        (new ConfigFactory())->create(__DIR__ . '/../', 'fixtures/config/invalidEnv.yml');
    }
}

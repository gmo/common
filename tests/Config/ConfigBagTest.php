<?php

namespace Gmo\Common\Tests\Config;

use Bolt\Collection\MutableBag;
use Gmo\Common\Config\ConfigBag;
use PHPUnit\Framework\TestCase;
use Webmozart\PathUtil\Path;

/**
 * @requires PHP 5.6
 */
class ConfigBagTest extends TestCase
{
    /** @var ConfigBag */
    private $config;

    protected function setUp()
    {
        $envs = MutableBag::fromRecursive(array(
            'default' => array(
                'test' => array(
                    'hello' => 'world',
                ),
            ),
            'production' => array(
                'test' => array(
                    'hello' => 'world2',
                    'list' => array(1, 2, 3),
                    'enabled' => 'yes',
                    'path' => '../foo/bar',
                    'key' => '%staging%',
                    'bad' => '%derp%',
                ),
            ),
            'staging' => array(
                'test' => array(
                    'hello' => 'world3',
                    'key' => 'value',
                ),
            ),
        ));

        $this->config = ConfigBag::root($envs, 'production', __DIR__);
    }

    public function testGet()
    {
        $this->assertEquals('world2', $this->config->get('test/hello'));
    }

    public function testGetDefault()
    {
        $this->assertNull($this->config->get('foo/bar', null));
        $this->assertEquals('default', $this->config->get('foo/bar', 'default'));
    }

    /**
     * @expectedException \GMO\Common\Exception\ConfigException
     * @expectedExceptionMessage Config value for key 'foo/bar' is missing.
     */
    public function testGetMissing()
    {
        $this->config->get('foo/bar');
    }

    public function testGetValueIsEnvAlias()
    {
        $this->assertEquals('value', $this->config->get('test/key'));
    }

    /**
     * @expectedException \GMO\Common\Exception\ConfigException
     * @expectedExceptionMessage Config does not contain the environment 'derp' requested by 'test/bad'.
     */
    public function testGetValueIsUnknownEnvAlias()
    {
        $this->config->get('test/bad');
    }

    public function testGetBag()
    {
        $actual = $this->config->getBag('test/list');
        $this->assertInstanceOf('Bolt\Collection\Bag', $actual);
        $this->assertEquals(array(1, 2, 3), $actual->toArray());
    }

    public function testGetBagDefault()
    {
        $actual = $this->config->getBag('test/derp', array(1, 2));
        $this->assertInstanceOf('Bolt\Collection\Bag', $actual);
        $this->assertEquals(array(1, 2), $actual->toArray());
    }

    public function testGetBool()
    {
        $this->assertTrue($this->config->getBool('test/enabled'));
    }

    public function testGetBoolDefault()
    {
        $this->assertTrue($this->config->getBool('test/derp', 'yes'));
    }

    public function testGetPath()
    {
        $actual = $this->config->getPath('test/path');
        $expected = Path::canonicalize(__DIR__ . '/../foo/bar');
        $this->assertEquals($expected, $actual);
    }

    public function testGetPathDefault()
    {
        $actual = $this->config->getPath('test/derp', 'nope');
        $expected = Path::canonicalize(__DIR__ . '/nope');
        $this->assertEquals($expected, $actual);
    }

    public function testGetSetEnv()
    {
        $this->assertEquals('production', $this->config->getEnv());

        $this->config->setEnv('staging');
        $this->assertEquals('staging', $this->config->getEnv());
    }

    /**
     * @expectedException \GMO\Common\Exception\ConfigException
     * @expectedExceptionMessage Config does not contain the environment 'derp'.
     */
    public function testSetEnvInvalid()
    {
        $this->config->setEnv('derp');
    }

    public function testWithEnv()
    {
        $config = $this->config->withEnv('staging');
        $this->assertInstanceOf('Gmo\Common\Config\ConfigBag', $config);
        $this->assertNotSame($this->config, $config);

        $this->assertEquals('production', $this->config->getEnv());
        $this->assertEquals('staging', $config->getEnv());
    }

    public function testGetWithEnv()
    {
        $this->assertEquals('world3', $this->config->withEnv('staging')->get('test/hello'));
    }

    /**
     * @expectedException \GMO\Common\Exception\ConfigException
     * @expectedExceptionMessage Config does not contain the environment 'derp'.
     */
    public function testWithUnknownEnv()
    {
        $this->config->withEnv('derp');
    }

    public function testSet()
    {
        $this->config->set('test/hello', 'world8');

        $this->assertEquals('world8', $this->config->get('test/hello'));
    }

    public function testChild()
    {
        $config = $this->config->child('test');
        $this->assertInstanceOf('Gmo\Common\Config\ConfigBag', $config);
        $this->assertNotSame($this->config, $config);

        $this->assertEquals('world2', $config->get('hello'));

        $config->set('enabled', 'no');
        $this->assertEquals('no', $this->config->get('test/enabled'), 'child should update parent');
    }

    public function testChildNull()
    {
        $config = $this->config->child('test', null);
        $this->assertInstanceOf('Gmo\Common\Config\ConfigBag', $config);
        $this->assertNotSame($this->config, $config);
    }

    public function testChildSubclass()
    {
        /** @var TestSubConfigBag $config */
        $config = $this->config->child('test', 'Gmo\Common\Tests\Config\TestSubConfigBag');
        $this->assertInstanceOf('Gmo\Common\Tests\Config\TestSubConfigBag', $config);

        $this->assertEquals('world2', $config->getHello());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected a sub-class of "Gmo\Common\Config\ConfigBag". Got: "ArrayObject"
     */
    public function testChildInvalidSubclass()
    {
        $this->config->child('test', 'ArrayObject');
    }
}

<?php

namespace Gmo\Common\Tests\Config;

use GMO\Common\Collections\ArrayCollection;
use Gmo\Common\Tests\Config\Singleton\CsvConfig;
use Gmo\Common\Tests\Config\Singleton\IniConfig;
use Gmo\Common\Tests\Config\Singleton\JsonConfig;
use Gmo\Common\Tests\Config\Singleton\NonexistentConfig;
use PHPUnit\Framework\TestCase;

/**
 * @group legacy
 */
class AbstractConfigTest extends TestCase
{
    public function test_get_json_value()
    {
        $this->assertEquals("git", JsonConfig::getRepoType());
    }

    public function test_get_ini_value()
    {
        $this->assertEquals(1, IniConfig::getAllowedAuthorization());
        $this->assertSame("dev", IniConfig::getValue(null, 'env'));
    }

    public function test_get_abs_path_from_config()
    {
        $expected = realpath(__DIR__ . '/../fixtures/config/testConfig.yml');
        $this->assertEquals($expected, IniConfig::getYamlFile());
    }

    public function test_get_path_default_value()
    {
        $this->assertEquals("nope", IniConfig::getUnknownFile());
    }

    public function test_allow_empty_value()
    {
        $this->assertSame("", IniConfig::getValue(null, "password", null, true));
        $this->assertSame("", JsonConfig::getValue(null, "homepage", "", true));
        $this->assertSame(null, JsonConfig::getValue(null, "bugs", null, true));
    }

    public function test_get_default_value()
    {
        $this->assertSame("defaultValue", IniConfig::getDefaultKey());
        $this->assertSame("defaultValue", IniConfig::getValue(null, "needed", "defaultValue"));
        $this->assertSame("defaultValue", IniConfig::getValue(null, "password", "defaultValue", true));
    }

    public function test_get_bool_string_true()
    {
        $this->assertTrue(IniConfig::getBool("SWITCHES", "debug"));
    }

    public function test_get_bool_string_false()
    {
        $this->assertFalse(IniConfig::getBool("SWITCHES", "other"));
    }

    public function test_get_bool_string_other()
    {
        $this->assertFalse(IniConfig::getBool("SWITCHES", "unknown"));
    }

    public function test_get_bool()
    {
        $this->assertTrue(IniConfig::getBool("SWITCHES", "live"));
        $this->assertFalse(IniConfig::getBool("SWITCHES", "nope"));
    }

    public function test_get_bool_int()
    {
        $this->assertTrue(IniConfig::getBool("SWITCHES", "live"));
        $this->assertFalse(IniConfig::getBool("SWITCHES", "nope"));
    }

    public function test_get_list()
    {
        $list = IniConfig::getList("LISTS", "life");
        $this->assertTrue($list instanceof ArrayCollection);
        $this->assertCount(4, $list);
    }

    public function test_get_list_default_value_array()
    {
        $default = array('hi');
        $list = IniConfig::getList("LISTS", "adsf", $default);
        $this->assertTrue($list instanceof ArrayCollection);
        $this->assertCount(1, $list);
        $this->assertSame('hi', $list->first());
    }

    public function test_get_list_default_value_array_collection()
    {
        $default = new ArrayCollection(array('hi'));
        $list = IniConfig::getList("LISTS", "adsf", $default);
        $this->assertTrue($list instanceof ArrayCollection);
        $this->assertCount(1, $list);
        $this->assertSame('hi', $list->first());
    }

    public function test_get_list_default_value_traversable()
    {
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
    public function test_get_nonexistent_list()
    {
        IniConfig::getList("LISTS", "listssss");
    }

    /**
     * @expectedException \GMO\Common\Exception\ConfigException
     */
    public function test_missing_key()
    {
        IniConfig::getMissingKey();
    }

    /**
     * @expectedException \GMO\Common\Exception\ConfigException
     * @expectedExceptionMessage Config file doesn't exist
     */
    public function test_nonexistent_config_file()
    {
        NonexistentConfig::getSomething();
    }

    /**
     * @expectedException \GMO\Common\Exception\ConfigException
     * @expectedExceptionMessage Unknown config file format
     */
    public function test_unknown_config_format()
    {
        CsvConfig::getSomething();
    }
}

<?php

namespace Gmo\Common\Tests\Config\Singleton;

use GMO\Common\Config\AbstractConfig;

class IniConfig extends AbstractConfig
{
    public static function setProjectDir() { return "../.."; }
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

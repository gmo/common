<?php

namespace Gmo\Common\Tests\Config\Singleton;

use GMO\Common\Config\AbstractConfig;

class CsvConfig extends AbstractConfig
{
    public static function setProjectDir() { return "../.."; }
    public static function setConfigFile() { return "fixtures/config/testConfig.csv"; }

    public static function getSomething() {
        return static::getValue("nope", "nope");
    }
}

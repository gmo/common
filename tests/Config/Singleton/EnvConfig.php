<?php

namespace Gmo\Common\Tests\Config\Singleton;

use GMO\Common\Config\EnvironmentAwareConfig;

class EnvConfig extends EnvironmentAwareConfig
{
    protected static $env;

    public static function getEnvironment() {
        return static::$env;
    }

    public static function setEnvironment($env) {
        static::$env = $env;
        static::doSetConfig();
    }

    public static function setProjectDir() { return "../.."; }
    public static function setConfigFile() { return "fixtures/config/testConfig.yml"; }
}

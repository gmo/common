<?php

namespace Gmo\Common\Tests\Config\Singleton;

use GMO\Common\Config\AbstractConfig;

class NonexistentConfig extends AbstractConfig
{
    public static function setProjectDir() { return "../.."; }
    public static function setConfigFile() { return "asdf"; }

    public static function getSomething() {
        return static::getValue("nope", "nope");
    }
}

<?php

namespace Gmo\Common\Tests\Config\Singleton;

use GMO\Common\Config\AbstractConfig;

class JsonConfig extends AbstractConfig
{
    public static function setProjectDir() { return "../.."; }
    public static function setConfigFile() { return "fixtures/config/package.json"; }

    public static function getRepoType() {
        return static::getValue("repository", "type");
    }
}
